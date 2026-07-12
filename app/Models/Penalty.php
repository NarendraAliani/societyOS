<?php

declare(strict_types=1);

namespace App\Models;

final class Penalty
{
    public static function forBill(int $billId): ?array
    {
        $stmt = db()->prepare(
            'SELECT * FROM penalties WHERE maintenance_bill_id = :bill_id ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute(['bill_id' => $billId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * One penalty row per bill — recalculation overwrites the existing row (identified by
     * ORDER BY id DESC LIMIT 1, since the table has no unique constraint on
     * maintenance_bill_id) rather than accumulating a new row on every view.
     */
    public static function upsert(int $billId, float $interestRatePercent, int $daysOverdue, float $penaltyAmount): void
    {
        $existing = self::forBill($billId);

        if ($existing) {
            $stmt = db()->prepare(
                'UPDATE penalties SET interest_rate_percent = :rate, days_overdue = :days,
                    penalty_amount = :amount, calculated_at = NOW()
                 WHERE id = :id'
            );
            $stmt->execute([
                'rate' => $interestRatePercent,
                'days' => $daysOverdue,
                'amount' => $penaltyAmount,
                'id' => $existing['id'],
            ]);
            return;
        }

        $stmt = db()->prepare(
            'INSERT INTO penalties (maintenance_bill_id, interest_rate_percent, days_overdue, penalty_amount)
             VALUES (:bill_id, :rate, :days, :amount)'
        );
        $stmt->execute([
            'bill_id' => $billId,
            'rate' => $interestRatePercent,
            'days' => $daysOverdue,
            'amount' => $penaltyAmount,
        ]);
    }
}
