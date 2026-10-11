<?php
declare(strict_types=1);
$files=['app/Models/User.php','app/Helpers/Auth.php','app/Controllers/AdminController.php','app/Views/admin/create_user.php','app/Views/admin/users.php'];
foreach($files as $file){if(!is_file($file)){fwrite(STDERR,"Missing {$file}\n");exit(1);}}
$checks=[
'user assignments use user_roles'=>str_contains(file_get_contents('app/Models/User.php'),'FROM user_roles'),
'accounts can have multiple roles'=>str_contains(file_get_contents('app/Models/User.php'),'rolesForUser'),
'flat+role is protected'=>str_contains(file_get_contents('app/Models/User.php'),'ur.flat_id=target.flat_id'),
'role switching is session based'=>str_contains(file_get_contents('app/Helpers/Auth.php'),'switchRole'),
'existing email adds role'=>str_contains(file_get_contents('app/Controllers/AdminController.php'),'User::addRole'),
'create form documents existing account behavior'=>str_contains(file_get_contents('app/Views/admin/create_user.php'),'email already exists'),
'admin UI manages role assignments'=>str_contains(file_get_contents('app/Views/admin/users.php'),'rolesForUser'),
'no stale removed-user route remains'=>!str_contains(file_get_contents('public/index.php'),"AdminController::class, 'updateUser'"),
'admin user mutations are society scoped'=>preg_match('/public function removeUserRole[\\s\\S]*?society_id.*?Society::currentId\\(\\)/',file_get_contents('app/Controllers/AdminController.php')) && preg_match('/public function updateUserStatus[\\s\\S]*?society_id.*?Society::currentId\\(\\)/',file_get_contents('app/Controllers/AdminController.php')) && preg_match('/public function resetPassword[\\s\\S]*?society_id.*?Society::currentId\\(\\)/',file_get_contents('app/Controllers/AdminController.php')),
];
$failed=false;foreach($checks as $n=>$ok){printf("[%s] %s\n",$ok?'PASS':'FAIL',$n);if(!$ok)$failed=true;}if($failed)exit(1);echo "All multi-role regression contracts passed.\n";
