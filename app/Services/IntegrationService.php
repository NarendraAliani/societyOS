<?php
declare(strict_types=1);
namespace App\Services;

use App\Helpers\SecretCipher;
use App\Models\MaintenanceBill;
use App\Models\Payment;
use App\Models\Settings;

final class IntegrationService
{
    private static function setting(int $societyId, string $key, string $default = ''): string
    {
        return (string) Settings::get($societyId, $key, $default);
    }

    public static function config(int $societyId): array
    {
        return [
            'razorpay_enabled' => self::setting($societyId, 'integration.razorpay.enabled', '0') === '1',
            'razorpay_key_id' => SecretCipher::decrypt(self::setting($societyId, 'integration.razorpay.key_id')),
            'razorpay_key_secret' => SecretCipher::decrypt(self::setting($societyId, 'integration.razorpay.key_secret')),
            'razorpay_webhook_secret' => SecretCipher::decrypt(self::setting($societyId, 'integration.razorpay.webhook_secret')),
            'upi_enabled' => self::setting($societyId, 'integration.upi.enabled', '0') === '1',
            'upi_id' => self::setting($societyId, 'integration.upi.id'),
            'upi_name' => self::setting($societyId, 'integration.upi.name'),
            'whatsapp_enabled' => self::setting($societyId, 'integration.whatsapp.enabled', '0') === '1',
            'whatsapp_phone_number_id' => self::setting($societyId, 'integration.whatsapp.phone_number_id'),
            'whatsapp_access_token' => SecretCipher::decrypt(self::setting($societyId, 'integration.whatsapp.access_token')),
            'whatsapp_verify_token' => SecretCipher::decrypt(self::setting($societyId, 'integration.whatsapp.verify_token')),
            'whatsapp_business_account_id' => self::setting($societyId, 'integration.whatsapp.business_account_id'),
            'telegram_enabled' => self::setting($societyId, 'integration.telegram.enabled', '0') === '1',
            'telegram_bot_token' => SecretCipher::decrypt(self::setting($societyId, 'integration.telegram.bot_token')),
            'telegram_default_chat_id' => self::setting($societyId, 'integration.telegram.default_chat_id'),
        ];
    }

    public static function createRazorpayOrder(int $societyId, int $billId, float $amount, int $memberId): array
    {
        $cfg = self::config($societyId);
        if (!$cfg['razorpay_enabled'] || $cfg['razorpay_key_id'] === '' || $cfg['razorpay_key_secret'] === '') {
            throw new \RuntimeException('Razorpay is not configured for this society.');
        }
        $bill = MaintenanceBill::find($billId);
        if (!$bill || (int) $bill['society_id'] !== $societyId) throw new \RuntimeException('Bill not found.');
        $memberBills = MaintenanceBill::forMember($memberId);
        $allowed = false;
        foreach ($memberBills as $row) if ((int)$row['id'] === $billId) { $allowed = true; break; }
        if (!$allowed) throw new \RuntimeException('You cannot pay this bill.');

        $outstanding = max(0.0, (float)$bill['total_amount'] - (float)$bill['paid_amount']);
        if ($amount <= 0 || $amount > $outstanding + 0.01) throw new \InvalidArgumentException('Payment amount is outside the bill outstanding amount.');

        $receipt = 'SOC' . $societyId . '-BILL' . $billId . '-' . date('YmdHis');
        $payload = json_encode([
            'amount' => (int) round($amount * 100),
            'currency' => 'INR',
            'receipt' => substr($receipt, 0, 40),
            'notes' => ['society_id' => (string)$societyId, 'bill_id' => (string)$billId, 'member_id' => (string)$memberId],
        ], JSON_THROW_ON_ERROR);

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $cfg['razorpay_key_id'] . ':' . $cfg['razorpay_key_secret'],
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($body === false || $status < 200 || $status >= 300) {
            throw new \RuntimeException('Razorpay order creation failed' . ($error ? ': ' . $error : '.'));
        }
        $order = json_decode((string)$body, true);
        if (!is_array($order) || empty($order['id'])) throw new \RuntimeException('Invalid Razorpay order response.');
        $_SESSION['societyos_razorpay_orders'][$order['id']] = ['bill_id'=>$billId,'member_id'=>$memberId,'amount'=>$amount,'created_at'=>time()];
        return ['order_id' => $order['id'], 'amount' => $order['amount'], 'currency' => $order['currency'], 'key_id' => $cfg['razorpay_key_id']];
    }

    public static function verifyRazorpayPayment(int $societyId, string $paymentId, string $signature, ?int $userId): int
    {
        $cfg = self::config($societyId);
        $orderId = '';
        $pending = [];
        foreach (($_SESSION['societyos_razorpay_orders'] ?? []) as $candidateOrderId => $record) {
            if (!empty($record['created_at']) && (time() - (int)$record['created_at']) <= 1800) {
                $pending[$candidateOrderId] = $record;
            }
        }
        $_SESSION['societyos_razorpay_orders'] = $pending;
        foreach ($pending as $candidateOrderId => $record) {
            if (!empty($record['payment_id']) && $record['payment_id'] === $paymentId) { $orderId = $candidateOrderId; break; }
        }
        if ($orderId === '' && !empty($_POST['razorpay_order_id']) && isset($pending[(string)$_POST['razorpay_order_id']])) {
            $orderId = (string)$_POST['razorpay_order_id'];
        }
        if ($orderId === '' || !isset($pending[$orderId])) throw new \RuntimeException('Razorpay order session expired. Please start payment again.');
        $record = $pending[$orderId];
        $billId = (int)$record['bill_id'];
        $memberId = (int)$record['member_id'];
        $amount = (float)$record['amount'];
        if ($cfg['razorpay_key_secret'] === '') throw new \RuntimeException('Razorpay secret is not configured.');
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $cfg['razorpay_key_secret']);
        if (!hash_equals($expected, $signature)) throw new \RuntimeException('Invalid Razorpay payment signature.');

        $bill = MaintenanceBill::find($billId);
        if (!$bill || (int)$bill['society_id'] !== $societyId) throw new \RuntimeException('Bill not found.');
        $allowed = false;
        foreach (MaintenanceBill::forMember($memberId) as $row) if ((int)$row['id'] === $billId) { $allowed = true; break; }
        if (!$allowed) throw new \RuntimeException('You cannot pay this bill.');

        $existing = Payment::findByReference($paymentId);
        if ($existing) return (int)$existing['id'];

        $ch = curl_init('https://api.razorpay.com/v1/payments/' . rawurlencode($paymentId));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => $cfg['razorpay_key_id'] . ':' . $cfg['razorpay_key_secret'],
        ]);
        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $status < 200 || $status >= 300) throw new \RuntimeException('Unable to confirm Razorpay payment status.');
        $payment = json_decode((string)$body, true);
        if (!is_array($payment) || ($payment['status'] ?? '') !== 'captured') {
            throw new \RuntimeException('Razorpay payment is not captured/authorized.');
        }
        $paidAmount = ((int)($payment['amount'] ?? 0)) / 100;
        if (abs($paidAmount - $amount) > 0.01) throw new \RuntimeException('Razorpay payment amount does not match the bill payment.');

        $mode = match((string)($payment['method'] ?? 'upi')) { 'card' => 'card', 'netbanking' => 'bank_transfer', 'upi' => 'upi', default => 'upi' };
        $result = BillingService::recordPayment($billId, $amount, $mode, $paymentId, $userId, $societyId);
        unset($_SESSION['societyos_razorpay_orders'][$orderId]);
        return $result['payment_id'];
    }

    public static function verifyWebhook(int $societyId, string $payload, string $signature): bool
    {
        $secret = self::config($societyId)['razorpay_webhook_secret'];
        if ($secret === '') return false;
        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }

    public static function sendWhatsAppText(int $societyId, string $recipient, string $message): array
    {
        $cfg = self::config($societyId);
        if (!$cfg['whatsapp_enabled'] || $cfg['whatsapp_phone_number_id'] === '' || $cfg['whatsapp_access_token'] === '') {
            throw new \RuntimeException('WhatsApp is not configured for this society.');
        }
        $url = 'https://graph.facebook.com/v23.0/' . rawurlencode($cfg['whatsapp_phone_number_id']) . '/messages';
        $payload = json_encode(['messaging_product'=>'whatsapp','to'=>$recipient,'type'=>'text','text'=>['preview_url'=>false,'body'=>$message]], JSON_THROW_ON_ERROR);
        return self::postJson($url, $payload, ['Authorization: Bearer ' . $cfg['whatsapp_access_token']]);
    }

    public static function sendTelegramText(int $societyId, string $chatId, string $message): array
    {
        $cfg = self::config($societyId);
        if (!$cfg['telegram_enabled'] || $cfg['telegram_bot_token'] === '') {
            throw new \RuntimeException('Telegram is not configured for this society.');
        }
        $url = 'https://api.telegram.org/bot' . rawurlencode($cfg['telegram_bot_token']) . '/sendMessage';
        return self::postJson($url, json_encode(['chat_id'=>$chatId,'text'=>$message,'protect_content'=>true], JSON_THROW_ON_ERROR), []);
    }

    private static function postJson(string $url, string $payload, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>array_merge(['Content-Type: application/json'], $headers),CURLOPT_POSTFIELDS=>$payload]);
        $body=curl_exec($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        $data=json_decode((string)$body,true);
        if ($body===false || $status<200 || $status>=300 || !is_array($data) || (($data['ok'] ?? true)===false)) throw new \RuntimeException('External messaging service request failed.');
        return $data;
    }
}
