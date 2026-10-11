<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Role;

final class SocietyProvisioner
{
    public static function provision(array $input): array
    {
        $code=strtoupper(trim((string)($input['code']??'')));
        $name=trim((string)($input['name']??''));
        $email=strtolower(trim((string)($input['admin_email']??'')));
        $adminName=trim((string)($input['admin_name']??''));
        $adminPhone=trim((string)($input['admin_phone']??''));
        $password=(string)($input['admin_password']??'');
        $fyStartYear=(int)($input['fy_start_year']??date('Y'));

        if(!preg_match('/^[A-Z0-9][A-Z0-9-]{2,29}$/',$code)) throw new \InvalidArgumentException('Society code must be 3-30 characters using letters, numbers, or hyphens.');
        if($name===''||$adminName===''||!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new \InvalidArgumentException('Society name, administrator name, and a valid administrator email are required.');
        if(strlen($password)<8) throw new \InvalidArgumentException('Administrator password must be at least 8 characters.');
        if($fyStartYear<2000||$fyStartYear>2100) throw new \InvalidArgumentException('Financial year start year is invalid.');

        $pdo=db(); $pdo->beginTransaction();
        try {
            $stmt=$pdo->prepare('SELECT id FROM society WHERE code=:code LIMIT 1'); $stmt->execute(['code'=>$code]);
            if($stmt->fetchColumn()) throw new \InvalidArgumentException('That society code is already in use.');

            $pdo->prepare('INSERT INTO society(code,name,phone,email) VALUES(:code,:name,:phone,:email)')
                ->execute(['code'=>$code,'name'=>$name,'phone'=>$adminPhone!==''?$adminPhone:null,'email'=>$email]);
            $societyId=(int)$pdo->lastInsertId();

            $fyLabel=sprintf('%d-%d',$fyStartYear,$fyStartYear+1);
            $pdo->prepare('INSERT INTO financial_years(society_id,label,start_date,end_date,is_current) VALUES(:sid,:label,:start,:end,1)')
                ->execute(['sid'=>$societyId,'label'=>$fyLabel,'start'=>sprintf('%d-04-01',$fyStartYear),'end'=>sprintf('%d-03-31',$fyStartYear+1)]);

            $settings=['currency_symbol'=>'INR','maintenance_due_day'=>'10','penalty_interest_rate_percent'=>'18'];
            $setting=$pdo->prepare('INSERT INTO settings(society_id,`key`,`value`) VALUES(:sid,:key,:value)');
            foreach($settings as $key=>$value) $setting->execute(['sid'=>$societyId,'key'=>$key,'value'=>$value]);

            $pdo->prepare('INSERT INTO accounts(society_id,name,account_type,opening_balance) VALUES(:sid,"Cash in Hand","cash",0)')->execute(['sid'=>$societyId]);
            $category=$pdo->prepare('INSERT INTO complaint_categories(society_id,name) VALUES(:sid,:name)');
            foreach(['Plumbing','Electrical','Housekeeping','Security','Other'] as $item) $category->execute(['sid'=>$societyId,'name'=>$item]);
            $assetCategory=$pdo->prepare('INSERT INTO asset_categories(society_id,name) VALUES(:sid,:name)');
            foreach(['Lift','Generator','Fire Safety','Water Pump','CCTV'] as $item) $assetCategory->execute(['sid'=>$societyId,'name'=>$item]);

            $role=Role::findByName('super_admin');
            if(!$role) throw new \RuntimeException('The system super_admin role is missing. Run the base seed data first.');
            $pdo->prepare('INSERT INTO users(society_id,role_id,member_id,name,email,phone,password_hash,status,must_change_password) VALUES(:sid,:rid,NULL,:name,:email,:phone,:hash,"active",1)')
                ->execute(['sid'=>$societyId,'rid'=>(int)$role['id'],'name'=>$adminName,'email'=>$email,'phone'=>$adminPhone!==''?$adminPhone:null,'hash'=>password_hash($password,PASSWORD_BCRYPT)]);
            $userId=(int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO user_roles(user_id,society_id,role_id,member_id,flat_id,is_default) VALUES(:uid,:sid,:rid,NULL,NULL,1)')
                ->execute(['uid'=>$userId,'sid'=>$societyId,'rid'=>(int)$role['id']]);

            $heads=[['General Maintenance','fixed',2000.00],['Sinking Fund','fixed',500.00],['Water Charges','fixed',300.00]];
            $head=$pdo->prepare('INSERT INTO maintenance_heads(society_id,name,calculation_type) VALUES(:sid,:name,:type)');
            $rate=$pdo->prepare('INSERT INTO maintenance_head_rates(maintenance_head_id,amount,effective_from) VALUES(:hid,:amount,:date)');
            foreach($heads as [$headName,$type,$amount]) { $head->execute(['sid'=>$societyId,'name'=>$headName,'type'=>$type]); $rate->execute(['hid'=>(int)$pdo->lastInsertId(),'amount'=>$amount,'date'=>sprintf('%d-04-01',$fyStartYear)]); }

            $pdo->commit();
            return ['society_id'=>$societyId,'code'=>$code,'admin_user_id'=>$userId,'financial_year'=>$fyLabel];
        } catch(\Throwable $e) { $pdo->rollBack(); throw $e; }
    }
}
