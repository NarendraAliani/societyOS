<?php

declare(strict_types=1);

$checks=[
'database/migrations/2026-10-01-platform-admin-provisioning.sql',
'app/Helpers/PlatformAuth.php',
'app/Middleware/PlatformAdminMiddleware.php',
'app/Models/PlatformAdmin.php',
'app/Services/SocietyProvisioner.php',
'app/Controllers/PlatformController.php',
'app/Views/platform/login.php',
'app/Views/platform/societies.php',
'app/Views/platform/create_society.php',
'app/Views/platform/password.php',
];
foreach($checks as $path){if(!is_file(__DIR__.'/../../'.$path)){fwrite(STDERR,"Missing platform provisioning file: {$path}\n");exit(1);}}
$service=file_get_contents(__DIR__.'/../../app/Services/SocietyProvisioner.php');
foreach(['financial_years','settings','complaint_categories','asset_categories','maintenance_heads','users','user_roles'] as $table){if(strpos($service,$table)===false){fwrite(STDERR,"Provisioner does not initialize {$table}.\n");exit(1);}}
$index=file_get_contents(__DIR__.'/../../public/index.php');
foreach(['/platform/login','/platform/societies','/platform/password'] as $route){if(strpos($index,$route)===false){fwrite(STDERR,"Missing platform route {$route}.\n");exit(1);}}
echo "Platform provisioning regression contract passed.\n";
