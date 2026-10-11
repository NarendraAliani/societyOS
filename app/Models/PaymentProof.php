<?php
declare(strict_types=1);
namespace App\Models;

final class PaymentProof
{
    public static function submit(int $societyId, int $memberId, int $billId, float $amount, string $platform, string $transactionId, ?string $screenshotPath, string $notes): int
    {
        $check = db()->prepare('SELECT b.id FROM maintenance_bills b JOIN members m ON m.flat_id=b.flat_id WHERE b.id=:bill AND b.society_id=:sid AND m.id=:member AND m.society_id=:sid2 AND m.status=\'active\'');
        $check->execute(['bill'=>$billId,'sid'=>$societyId,'member'=>$memberId,'sid2'=>$societyId]);
        if (!$check->fetchColumn()) throw new \InvalidArgumentException('That bill is not linked to your home.');
        $billStmt=db()->prepare('SELECT total_amount,paid_amount FROM maintenance_bills WHERE id=:bill AND society_id=:sid');
        $billStmt->execute(['bill'=>$billId,'sid'=>$societyId]);
        $bill=$billStmt->fetch();
        $outstanding=$bill ? max(0.0,(float)$bill['total_amount']-(float)$bill['paid_amount']) : 0.0;
        if ($amount<=0 || $amount>$outstanding+0.00001) throw new \InvalidArgumentException('Enter an amount within the bill outstanding balance.');
        $pending = db()->prepare("SELECT id FROM payment_proofs WHERE maintenance_bill_id=:bill AND member_id=:member AND status='pending' LIMIT 1");
        $pending->execute(['bill'=>$billId,'member'=>$memberId]);
        if ($pending->fetchColumn()) throw new \InvalidArgumentException('You already have a payment proof awaiting verification for this bill.');
        $stmt=db()->prepare("INSERT INTO payment_proofs (society_id, maintenance_bill_id, member_id, submitted_by, amount, platform, transaction_id, screenshot_path, notes, status) VALUES (:sid,:bill,:member,:user,:amount,:platform,:txn,:path,:notes,'pending')");
        $stmt->execute(['sid'=>$societyId,'bill'=>$billId,'member'=>$memberId,'user'=>\App\Helpers\Auth::id(),'platform'=>$platform,'txn'=>$transactionId,'path'=>$screenshotPath,'notes'=>$notes !== '' ? $notes : null]);
        return (int)db()->lastInsertId();
    }

    public static function forMember(int $societyId, int $memberId): array
    {
        $stmt=db()->prepare('SELECT pp.*, b.bill_number FROM payment_proofs pp JOIN maintenance_bills b ON b.id=pp.maintenance_bill_id WHERE pp.society_id=:sid AND pp.member_id=:member ORDER BY pp.created_at DESC');
        $stmt->execute(['sid'=>$societyId,'member'=>$memberId]); return $stmt->fetchAll();
    }

    public static function pendingForSociety(int $societyId): array
    {
        $stmt=db()->prepare('SELECT pp.*, b.bill_number, b.total_amount, b.paid_amount, m.name AS member_name, f.flat_number, w.name AS wing_name FROM payment_proofs pp JOIN maintenance_bills b ON b.id=pp.maintenance_bill_id JOIN members m ON m.id=pp.member_id JOIN flats f ON f.id=b.flat_id JOIN floors fl ON fl.id=f.floor_id JOIN wings w ON w.id=fl.wing_id WHERE pp.society_id=:sid AND pp.status=\'pending\' ORDER BY pp.created_at ASC');
        $stmt->execute(['sid'=>$societyId]); return $stmt->fetchAll();
    }

    public static function findForSociety(int $id, int $societyId): ?array
    {
        $stmt=db()->prepare('SELECT pp.*, b.bill_number, b.total_amount, b.paid_amount, m.name AS member_name FROM payment_proofs pp JOIN maintenance_bills b ON b.id=pp.maintenance_bill_id JOIN members m ON m.id=pp.member_id WHERE pp.id=:id AND pp.society_id=:sid LIMIT 1');
        $stmt->execute(['id'=>$id,'sid'=>$societyId]); return $stmt->fetch() ?: null;
    }

    public static function review(int $id, int $societyId, int $reviewerId, string $status, string $notes, ?int $paymentId): void
    {
        $stmt=db()->prepare("UPDATE payment_proofs SET status=:status, reviewed_by=:reviewer, reviewed_at=NOW(), review_notes=:notes, payment_id=:payment WHERE id=:id AND society_id=:sid AND status='pending'");
        $stmt->execute(['status'=>$status,'reviewer'=>$reviewerId,'notes'=>$notes !== '' ? $notes : null,'payment'=>$paymentId,'id'=>$id,'sid'=>$societyId]);
        if ($stmt->rowCount() !== 1) throw new \RuntimeException('This proof has already been reviewed or no longer exists.');
    }

    public static function fileForSociety(int $id, int $societyId): ?array
    {
        $stmt=db()->prepare('SELECT screenshot_path, member_id FROM payment_proofs WHERE id=:id AND society_id=:sid');
        $stmt->execute(['id'=>$id,'sid'=>$societyId]); return $stmt->fetch() ?: null;
    }
}
