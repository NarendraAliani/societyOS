<?php
declare(strict_types=1);

$files = ['app/Helpers/Auth.php','app/Models/Society.php','app/Controllers/AuthController.php','app/Views/auth/login.php'];
foreach ($files as $file) {
    if (!is_file($file)) { fwrite(STDERR, "Missing required file: {$file}\n"); exit(1); }
}
$auth=file_get_contents('app/Helpers/Auth.php');
$society=file_get_contents('app/Models/Society.php');
$controller=file_get_contents('app/Controllers/AuthController.php');
$view=file_get_contents('app/Views/auth/login.php');
$checks=[
 'Auth exposes the logged-in society id'=>str_contains($auth,'societyId()'),
 'Society current context reads authenticated society id'=>str_contains($society,'Auth::societyId()'),
 'Society supports code lookup'=>str_contains($society,'findByCode'),
 'login reads the society code'=>str_contains($controller,'society_code'),
 'login resolves society by code'=>str_contains($controller,'Society::findByCode'),
 'rate limiting is scoped to the selected society'=>str_contains($controller,'u.society_id = :society_id'),
 'login view exposes society code'=>str_contains($view,'name="society_code"'),
];
$failed=false;
foreach($checks as $name=>$passed){printf("[%s] %s\n",$passed?'PASS':'FAIL',$name);if(!$passed)$failed=true;}
if($failed){fwrite(STDERR,"\nMulti-society context regression detected.\n");exit(1);}
echo "\nAll multi-society context regression contracts passed.\n";
