<?php

declare(strict_types=1);

namespace App\Models;

final class ParkingRate
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT r.*, u.name AS created_by_name
             FROM parking_rates r
             LEFT JOIN users u ON u.id = r.created_by
             WHERE r.society_id = :sid
             ORDER BY r.slot_type, r.effective_from DESC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM parking_rates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * The rate effective as of a given date (defaults to today) for a slot type.
     * Returns null if no rate has taken effect yet — that type is simply not charged.
     */
    public static function amountAsOf(int $societyId, string $slotType, ?string $date = null): ?float
    {
        $stmt = db()->prepare(
            'SELECT amount FROM parking_rates
             WHERE society_id = :sid AND slot_type = :slot_type AND effective_from <= :date
             ORDER BY effective_from DESC LIMIT 1'
        );
        $stmt->execute(['sid' => $societyId, 'slot_type' => $slotType, 'date' => $date ?? date('Y-m-d')]);
        $amount = $stmt->fetchColumn();
        return $amount !== false ? (float) $amount : null;
    }

    public static function create(int $societyId, string $slotType, float $amount, string $effectiveFrom, ?int $createdBy): int
    {
        $stmt = db()->prepare(
            'INSERT INTO parking_rates (society_id, slot_type, amount, effective_from, created_by)
             VALUES (:sid, :slot_type, :amount, :effective_from, :created_by)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'slot_type' => $slotType,
            'amount' => $amount,
            'effective_from' => $effectiveFrom,
            'created_by' => $createdBy,
        ]);
        return (int) db()->lastInsertId();
    }

    /** Same append-only rule as MaintenanceHeadRate — only a not-yet-effective rate can be removed. */
    public static function deleteIfFuture(int $id): bool
    {
        $rate = self::find($id);
        if (!$rate || strtotime((string) $rate['effective_from']) <= strtotime('today')) {
            return false;
        }

        $stmt = db()->prepare('DELETE FROM parking_rates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return true;
    }
}
