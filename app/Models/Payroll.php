<?php

declare(strict_types=1);

namespace App\Models;

final class Payroll
{
    public static function forStaff(int $staffId): array
    {
        $stmt = db()->prepare('SELECT * FROM payroll WHERE staff_id = :staff_id ORDER BY pay_period DESC');
        $stmt->execute(['staff_id' => $staffId]);
        return $stmt->fetchAll();
    }

    public static function create(int $staffId, string $payPeriod, float $basic, float $deductions): int
    {
        $net = $basic - $deductions;
        $stmt = db()->prepare(
            'INSERT INTO payroll (staff_id, pay_period, basic_amount, deductions, net_amount)
             VALUES (:staff_id, :period, :basic, :deductions, :net)'
        );
        $stmt->execute(['staff_id' => $staffId, 'period' => $payPeriod, 'basic' => $basic, 'deductions' => $deductions, 'net' => $net]);
        return (int) db()->lastInsertId();
    }

    public static function markPaid(int $id): void
    {
        $stmt = db()->prepare('UPDATE payroll SET paid_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
