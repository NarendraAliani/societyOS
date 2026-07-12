<?php

declare(strict_types=1);

namespace App\Models;

final class Receipt
{
    public static function create(int $paymentId, string $receiptNumber): int
    {
        $stmt = db()->prepare('INSERT INTO receipts (payment_id, receipt_number) VALUES (:payment_id, :receipt_number)');
        $stmt->execute(['payment_id' => $paymentId, 'receipt_number' => $receiptNumber]);
        return (int) db()->lastInsertId();
    }

    public static function findByPayment(int $paymentId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM receipts WHERE payment_id = :payment_id');
        $stmt->execute(['payment_id' => $paymentId]);
        return $stmt->fetch() ?: null;
    }

    public static function detail(int $receiptId): ?array
    {
        $stmt = db()->prepare(
            'SELECT r.*, p.amount, p.payment_mode, p.reference_number, p.paid_at,
                    b.bill_number, b.flat_id, f.flat_number, fl.floor_number, w.name AS wing_name,
                    b.society_id
             FROM receipts r
             JOIN payments p ON p.id = r.payment_id
             JOIN maintenance_bills b ON b.id = p.maintenance_bill_id
             JOIN flats f ON f.id = b.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE r.id = :id'
        );
        $stmt->execute(['id' => $receiptId]);
        return $stmt->fetch() ?: null;
    }
}
