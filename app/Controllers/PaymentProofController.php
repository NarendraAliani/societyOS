<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\FileUpload;
use App\Helpers\Flash;
use App\Helpers\Mailer;
use App\Models\ActivityLog;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\PaymentProof;
use App\Models\Society;
use App\Services\BillingService;

final class PaymentProofController
{
    public function submit(): void
    {
        $this->csrf();
        $memberId=Auth::memberId();
        if (!$memberId) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }
        $billId=(int)($_POST['bill_id']??0);
        $bill=MaintenanceBill::find($billId);
        if (!$bill || !Member::find($memberId)) { Flash::set('error','Bill not found for your society.'); header('Location:/resident/bills'); exit; }
        $platform=trim((string)($_POST['platform']??''));
        $transactionId=trim((string)($_POST['transaction_id']??''));
        $amount=is_numeric($_POST['amount']??null)?(float)$_POST['amount']:0.0;
        $notes=trim((string)($_POST['notes']??''));
        if (!in_array($platform,['UPI app','Bank transfer','Debit/Credit card','Other'],true) || $transactionId==='' || strlen($transactionId)>100 || strlen($notes)>1000) {
            Flash::set('error','Choose a payment platform and enter a valid transaction/reference ID.'); header('Location:/resident/upi-qr/'.$billId); exit;
        }
        $path=null;
        try {
            if (!empty($_FILES['screenshot']['name'])) $path=FileUpload::storeImage($_FILES['screenshot'],'payment-proofs');
            PaymentProof::submit(Society::currentId(),$memberId,$billId,$amount,$platform,$transactionId,$path,$notes);
            ActivityLog::log('billing','payment_proof_submitted',"Payment proof submitted for bill {$bill['bill_number']}");
            $this->notifyBillingManagers(
                Society::currentId(),
                'Payment proof submitted',
                sprintf('A resident submitted payment proof for bill %s, transaction %s, amount ₹%s.', $bill['bill_number'], $transactionId, number_format($amount, 2)),
                '/billing/payment-proofs',
                'Review payment proof'
            );
            Flash::set('success','Payment proof submitted. The society team has been notified; your bill remains unpaid until verification.');
        } catch (\Throwable $e) {
            Flash::set('error',$e instanceof \InvalidArgumentException ? $e->getMessage() : 'Could not submit payment proof. Please check the details and try again.');
        }
        header('Location:/resident/bills'); exit;
    }

    public function mine(): void
    {
        $memberId=Auth::memberId();
        if (!$memberId) { http_response_code(403); require __DIR__.'/../Views/errors/403.php'; return; }
        $pageTitle='My Payment Proofs';
        $proofs=PaymentProof::forMember(Society::currentId(),$memberId);
        require __DIR__.'/../Views/resident/payment_proofs.php';
    }

    public function index(): void
    {
        $pageTitle='Payment Proof Verification';
        $proofs=PaymentProof::pendingForSociety(Society::currentId());
        require __DIR__.'/../Views/billing/payment_proofs.php';
    }

    public function review(string $id): void
    {
        $this->csrf();
        $proof=PaymentProof::findForSociety((int)$id,Society::currentId());
        if (!$proof || $proof['status']!=='pending') { Flash::set('error','Payment proof not found or already reviewed.'); header('Location:/billing/payment-proofs'); exit; }
        $decision=(string)($_POST['decision']??'');
        $notes=trim((string)($_POST['review_notes']??''));
        if (!in_array($decision,['approve','reject'],true) || strlen($notes)>1000 || ($decision==='reject' && $notes==='')) {
            Flash::set('error','Choose approve/reject; a reason is required when rejecting.'); header('Location:/billing/payment-proofs'); exit;
        }
        $paymentId=null;
        try {
            if ($decision==='approve') {
                $amount=(float)$proof['amount'];
                if ($amount<=0) throw new \RuntimeException('This bill has no outstanding balance. Reject this proof and review the bill manually.');
                $mode=match($proof['platform']) {'Bank transfer'=>'bank_transfer','Debit/Credit card'=>'card',default=>'upi'};
                $result=BillingService::recordPayment((int)$proof['maintenance_bill_id'],$amount,$mode,$proof['transaction_id'],Auth::id(),Society::currentId());
                $paymentId=(int)$result['payment_id'];
            }
            PaymentProof::review((int)$id,Society::currentId(),(int)Auth::id(),$decision==='approve'?'approved':'rejected',$notes,$paymentId);
            ActivityLog::log('billing','payment_proof_'.$decision,"Payment proof #{$id} for bill {$proof['bill_number']} ".($decision==='approve'?'approved and recorded':'rejected'));
            $this->notifyResident(
                (int)$proof['member_id'],
                Society::currentId(),
                $decision === 'approve' ? 'Payment proof approved' : 'Payment proof rejected',
                sprintf('Your payment proof for bill %s was %s.%s', $proof['bill_number'], $decision === 'approve' ? 'approved and recorded' : 'rejected', $notes !== '' ? ' Review note: '.$notes : ''),
                '/resident/payment-proofs',
                'View payment proof status'
            );
            Flash::set('success',$decision==='approve'?'Proof approved and payment recorded; receipt issued.':'Proof rejected. The resident has been notified and can review the reason and resubmit.');
        } catch (\Throwable $e) {
            Flash::set('error',$e instanceof \InvalidArgumentException ? $e->getMessage() : ($e instanceof \RuntimeException ? $e->getMessage() : 'Unable to process this proof right now.'));
        }
        header('Location:/billing/payment-proofs'); exit;
    }

    public function file(string $id): void
    {
        $proof=PaymentProof::fileForSociety((int)$id,Society::currentId());
        if (!$proof || empty($proof['screenshot_path'])) { http_response_code(404); exit('File not found.'); }
        if (Auth::isResident() && (int)$proof['member_id'] !== (int)Auth::memberId()) { http_response_code(404); exit('File not found.'); }
        $path=dirname(__DIR__,2).'/uploads/'.$proof['screenshot_path'];
        if (!is_file($path)) { http_response_code(404); exit('File not found.'); }
        $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime,['image/jpeg','image/png'],true)) { http_response_code(404); exit('File not found.'); }
        header('Content-Type: '.$mime); header('Content-Length: '.(string)filesize($path)); header('X-Content-Type-Options: nosniff'); header('Content-Disposition: inline; filename="payment-proof-'.$id.'"');
        readfile($path); exit;
    }

    private function notifyBillingManagers(int $societyId, string $subject, string $message, string $path, string $actionLabel): void
    {
        try {
            $stmt = db()->prepare(
                "SELECT DISTINCT u.name, u.email
                 FROM users u
                 JOIN user_roles ur ON ur.user_id = u.id AND ur.society_id = :sid
                 JOIN role_permissions rp ON rp.role_id = ur.role_id
                 JOIN permissions p ON p.id = rp.permission_id AND p.key = 'billing.manage'
                 WHERE u.society_id = :sid2 AND u.status = 'active' AND u.email <> ''"
            );
            $stmt->execute(['sid' => $societyId, 'sid2' => $societyId]);
            $baseUrl = rtrim((string) (config()['url'] ?? ''), '/');
            foreach ($stmt->fetchAll() as $recipient) {
                try {
                    Mailer::sendNotification((string)$recipient['email'], (string)$recipient['name'], $subject, $message, $baseUrl.$path, $actionLabel);
                } catch (\Throwable $mailError) {
                    error_log('SocietyOS payment proof notification email failed: '.$mailError->getMessage());
                }
            }
        } catch (\Throwable $e) {
            error_log('SocietyOS payment proof admin notification lookup failed: '.$e->getMessage());
        }
    }

    private function notifyResident(int $memberId, int $societyId, string $subject, string $message, string $path, string $actionLabel): void
    {
        try {
            $stmt = db()->prepare(
                "SELECT m.name, COALESCE(NULLIF(m.email, ''), u.email) AS email
                 FROM members m
                 LEFT JOIN user_roles ur ON ur.member_id = m.id AND ur.society_id = m.society_id
                 LEFT JOIN users u ON u.id = ur.user_id AND u.society_id = m.society_id AND u.status = 'active'
                 WHERE m.id = :member AND m.society_id = :sid
                 ORDER BY ur.is_default DESC LIMIT 1"
            );
            $stmt->execute(['member' => $memberId, 'sid' => $societyId]);
            $recipient = $stmt->fetch();
            if (!$recipient || !filter_var((string)$recipient['email'], FILTER_VALIDATE_EMAIL)) return;
            $baseUrl = rtrim((string) (config()['url'] ?? ''), '/');
            Mailer::sendNotification((string)$recipient['email'], (string)$recipient['name'], $subject, $message, $baseUrl.$path, $actionLabel);
        } catch (\Throwable $e) {
            error_log('SocietyOS payment proof resident notification failed: '.$e->getMessage());
        }
    }

    private function csrf(): void
    {
        if (!Csrf::verify($_POST['_csrf']??null)) { http_response_code(419); exit('Session expired. Go back and try again.'); }
    }
}
