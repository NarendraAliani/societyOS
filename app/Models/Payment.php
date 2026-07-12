<?php

declare(strict_types=1);

namespace App\Models;

final class Payment
{
    public static function create(int $billId, float $amount, string $mode, ?string $reference, ?int $receivedBy): int
    {
        $stmt = db()->prepare(
            'INSERT INTO payments (maintenance_bill_id, amount, payment_mode, reference_number, received_by)
             VALUES (:bill_id, :amount, :mode, :reference, :received_by)'
        );
        $stmt->execute([
            'bill_id' => $billId,
            'amount' => $amount,
            'mode' => $mode,
            'reference' => $reference,
            'received_by' => $receivedBy,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM payments WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
