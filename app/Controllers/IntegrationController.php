<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Helpers\SecretCipher;
use App\Models\ActivityLog;
use App\Models\Settings;
use App\Models\Society;
use App\Services\IntegrationService;
final class IntegrationController
{
    private const SECRET_FIELDS=['razorpay_key_id','razorpay_key_secret','razorpay_webhook_secret','whatsapp_access_token','whatsapp_verify_token','telegram_bot_token'];
    public function index(): void
    {
        $pageTitle='Integrations & Payments';
        $cfg=IntegrationService::config(Society::currentId());
        $masked=[];
        foreach(self::SECRET_FIELDS as $key) $masked[$key]=SecretCipher::masked((string)($cfg[$key]??''));
        require __DIR__.'/../Views/admin/integrations.php';
    }
    public function toggle(): void
    {
        $this->csrf();
        $sid = Society::currentId();
        $name = (string)($_POST['integration'] ?? '');
        $allowed = ['razorpay','upi','whatsapp','telegram'];
        if (!in_array($name, $allowed, true)) {
            http_response_code(422);
            $this->json(['ok'=>false,'message'=>'Invalid integration.']);
        }
        $enabled = (string)($_POST['enabled'] ?? '0') === '1' ? '1' : '0';
        Settings::set($sid, 'integration.'.$name.'.enabled', $enabled);
        ActivityLog::log('settings','integration_toggle',sprintf('Set %s integration %s', $name, $enabled === '1' ? 'enabled' : 'disabled'));
        $this->json(['ok'=>true,'integration'=>$name,'enabled'=>$enabled === '1']);
    }

    public function update(): void
    {
        $this->csrf();
        $sid=Society::currentId();
        foreach(['razorpay','upi','whatsapp','telegram'] as $name) Settings::set($sid,'integration.'.$name.'.enabled',isset($_POST[$name.'_enabled'])?'1':'0');
        $fields=[
            'integration.razorpay.key_id'=>['razorpay_key_id',true],
            'integration.razorpay.key_secret'=>['razorpay_key_secret',true],
            'integration.razorpay.webhook_secret'=>['razorpay_webhook_secret',true],
            'integration.upi.id'=>['upi_id',false],
            'integration.upi.name'=>['upi_name',false],
            'integration.whatsapp.phone_number_id'=>['whatsapp_phone_number_id',false],
            'integration.whatsapp.access_token'=>['whatsapp_access_token',true],
            'integration.whatsapp.verify_token'=>['whatsapp_verify_token',true],
            'integration.whatsapp.business_account_id'=>['whatsapp_business_account_id',false],
            'integration.telegram.bot_token'=>['telegram_bot_token',true],
            'integration.telegram.default_chat_id'=>['telegram_default_chat_id',false],
        ];
        foreach($fields as $setting=>[$post,$secret]){
            $value=trim((string)($_POST[$post]??''));
            if($secret){
                if($value!=='') Settings::set($sid,$setting,SecretCipher::encrypt($value));
            } else Settings::set($sid,$setting,$value);
        }
        ActivityLog::log('settings','integration_update','Updated society payment and messaging integrations');
        Flash::set('success','Integration settings saved. Secrets are encrypted at rest.');
        header('Location:/admin/integrations'); exit;
    }
    public function testWhatsApp(): void
    {
        $this->csrf();
        try{IntegrationService::sendWhatsAppText(Society::currentId(),trim((string)($_POST['recipient']??'')),'SocietyOS test message — WhatsApp integration is working.');Flash::set('success','WhatsApp test sent.');}
        catch(\Throwable $e){Flash::set('error',$e->getMessage());}
        header('Location:/admin/integrations');exit;
    }
    public function testTelegram(): void
    {
        $this->csrf();
        try{IntegrationService::sendTelegramText(Society::currentId(),trim((string)($_POST['chat_id']??'')),'SocietyOS test message — Telegram integration is working.');Flash::set('success','Telegram test sent.');}
        catch(\Throwable $e){Flash::set('error',$e->getMessage());}
        header('Location:/admin/integrations');exit;
    }
    public function razorpayOrder(): void
    {
        $this->csrf();
        try{$result=IntegrationService::createRazorpayOrder(Society::currentId(),(int)($_POST['bill_id']??0),(float)($_POST['amount']??0),(int)(Auth::memberId()??0));$this->json(['ok'=>true]+$result);}
        catch(\Throwable $e){http_response_code(422);$this->json(['ok'=>false,'message'=>$e->getMessage()]);}
    }
    public function razorpayVerify(): void
    {
        $this->csrf();
        try{$id=IntegrationService::verifyRazorpayPayment(Society::currentId(),(string)($_POST['razorpay_payment_id']??''),(string)($_POST['razorpay_signature']??''),Auth::id());$this->json(['ok'=>true,'payment_id'=>$id]);}
        catch(\Throwable $e){http_response_code(422);$this->json(['ok'=>false,'message'=>$e->getMessage()]);}
    }
    public function razorpayWebhook(): void
    {
        $sid=(int)($_GET['society_id']??0);$payload=file_get_contents('php://input')?:'';$sig=$_SERVER['HTTP_X_RAZORPAY_SIGNATURE']??'';
        if($sid<=0){http_response_code(401);exit('Invalid webhook.');}
        try{IntegrationService::processRazorpayWebhook($sid,$payload,$sig);}catch(\Throwable $e){http_response_code(401);exit('Invalid webhook.');}
        http_response_code(200);echo 'ok';
    }
    public function whatsappWebhook(): void
    {
        $sid=(int)($_GET['society_id']??0);
        $mode=(string)($_GET['hub_mode']??$_GET['hub.mode']??'');
        $token=(string)($_GET['hub_verify_token']??$_GET['hub.verify_token']??'');
        $challenge=(string)($_GET['hub_challenge']??$_GET['hub.challenge']??'');
        $cfg=IntegrationService::config($sid);
        if($sid>0 && $mode==='subscribe' && $cfg['whatsapp_verify_token']!=='' && hash_equals($cfg['whatsapp_verify_token'],$token)){header('Content-Type:text/plain');echo $challenge;exit;}
        http_response_code(403);exit('Verification failed.');
    }

    public function upiQr(string $billId): void
    {
        $cfg=IntegrationService::config(Society::currentId());
        $bill=\App\Models\MaintenanceBill::find((int)$billId);
        if(!$cfg['upi_enabled'] || $cfg['upi_id']==='' || !$bill || (int)$bill['society_id']!==Society::currentId()){http_response_code(404);exit;}
        $allowed=false; foreach(\App\Models\MaintenanceBill::forMember((int)(Auth::memberId()??0)) as $row) if((int)$row['id']===(int)$billId){$allowed=true;break;}
        $amount=max(0,(float)$bill['total_amount']-(float)$bill['paid_amount']); if(!$allowed||$amount<=0){http_response_code(404);exit;}
        $uri='upi://pay?'.http_build_query(['pa'=>$cfg['upi_id'],'pn'=>$cfg['upi_name']?:'SocietyOS Society','am'=>number_format($amount,2,'.',''),'cu'=>'INR','tn'=>'Maintenance '.$bill['bill_number']], '', '&', PHP_QUERY_RFC3986);
        header('Content-Type:image/svg+xml; charset=UTF-8'); echo \App\Helpers\QrCode::svg($uri,320); exit;
    }

    private function csrf(): void{if(!Csrf::verify($_POST['_csrf']??null)){http_response_code(419);exit('Session expired. Go back and try again.');}}
    private function json(array $data):void{header('Content-Type: application/json; charset=UTF-8');echo json_encode($data,JSON_UNESCAPED_SLASHES);exit;}
}
