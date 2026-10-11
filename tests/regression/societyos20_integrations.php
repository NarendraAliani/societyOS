<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);
$files=[
'app/Helpers/SecretCipher.php',
'app/Services/IntegrationService.php',
'app/Controllers/IntegrationController.php',
'app/Views/admin/integrations.php',
];
foreach($files as $file){if(!is_file($root.'/'.$file))throw new RuntimeException("Missing {$file}");}
$routes=file_get_contents($root.'/public/index.php');
foreach(['/admin/integrations','/admin/integrations/toggle','/payments/razorpay/order','/payments/razorpay/verify','/payments/razorpay/webhook','/integrations/whatsapp/webhook','/resident/upi-qr/{billId}'] as $route){
 if(strpos($routes,$route)===false)throw new RuntimeException("Missing route {$route}");
}
$service=file_get_contents($root.'/app/Services/IntegrationService.php');
foreach(['https://api.razorpay.com/v1/orders','hash_hmac','payment.captured','https://graph.facebook.com','https://api.telegram.org'] as $contract){
 if(strpos($service,$contract)===false)throw new RuntimeException("Missing integration contract {$contract}");
}
$controller=file_get_contents($root.'/app/Controllers/IntegrationController.php');
if (strpos($controller, "integration_section") === false) throw new RuntimeException('Integration update must identify the submitted card.');
$view=file_get_contents($root.'/app/Views/admin/integrations.php');
foreach(['razorpay_key_id','razorpay_key_secret','razorpay_webhook_secret','upi_id','whatsapp_phone_number_id','whatsapp_access_token','telegram_bot_token','telegram_default_chat_id'] as $field){
 if(strpos($view,$field)===false)throw new RuntimeException("Missing settings field {$field}");
}
echo "SocietyOS 2.0 integration regression checks passed.\n";
