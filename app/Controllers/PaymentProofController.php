<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\FileUpload;
use App\Helpers\Flash;
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
        $notes=trim((string)($_POST['notes']??''));
        if (!in_array($platform,['UPI app','Bank transfer','Debit/Credit card','Other'],true) || $transactionId==='' || strlen($transactionId)>100 || strlen($notes)>1000) {
            Flash::set('error','Choose a payment platform and enter a valid transaction/reference ID.'); header('Location:/resident/upi-qr/'.$billId); exit;
        }
        $path=null;
        try {
            if (!empty($_FILES['screenshot']['name'])) $path=FileUpload::storeImage($_FILES['screenshot'],'payment-proofs');
            PaymentProof::submit(Society::currentId(),$memberId,$billId,$platform,$transactionId,$path,$notes);
            ActivityLog::log('billing','payment_proof_submitted',"Payment proof submitted for bill {$bill['bill_number']}");
            Flash::set('success','Payment proof submitted. Your bill will remain unpaid until the society verifies it.');
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
                $amount=(float)$proof['total_amount']-(float)$proof['paid_amount'];
                if ($amount<=0) throw new \RuntimeException('This bill has no outstanding balance. Reject this proof and review the bill manually.');
                $result=BillingService::recordPayment((int)$proof['maintenance_bill_id'],$amount,'upi',$proof['transaction_id'],Auth::id(),Society::currentId());
                $paymentId=(int)$result['payment_id'];
            }
            PaymentProof::review((int)$id,Society::currentId(),(int)Auth::id(),$decision==='approve'?'approved':'rejected',$notes,$paymentId);
            ActivityLog::log('billing','payment_proof_'.$decision,"Payment proof #{$id} for bill {$proof['bill_number']} ".($decision==='approve'?'approved and recorded':'rejected'));
            Flash::set('success',$decision==='approve'?'Proof approved and payment recorded; receipt issued.':'Proof rejected. The resident can review the reason and resubmit.');
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

    private function csrf(): void
    {
        if (!Csrf::verify($_POST['_csrf']??null)) { http_response_code(419); exit('Session expired. Go back and try again.'); }
    }
}
