<?php

declare(strict_types=1);

namespace App\Models;

final class ParkingAllocation
{
    public static function activeForSlot(int $slotId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM parking_allocations WHERE parking_slot_id = :slot_id AND allocated_to IS NULL LIMIT 1');
        $stmt->execute(['slot_id' => $slotId]);
        return $stmt->fetch() ?: null;
    }

    public static function historyForSlot(int $slotId): array
    {
        $stmt = db()->prepare(
            'SELECT pa.*, f.flat_number, w.name AS wing_name, v.registration_number
             FROM parking_allocations pa
             JOIN flats f ON f.id = pa.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             LEFT JOIN vehicles v ON v.id = pa.vehicle_id
             WHERE pa.parking_slot_id = :slot_id
             ORDER BY pa.allocated_from DESC'
        );
        $stmt->execute(['slot_id' => $slotId]);
        return $stmt->fetchAll();
    }

    /**
     * A flat's parking allocations that overlap the given billing period at all (any day
     * in common — billing doesn't prorate, so an allocation touching the period at all
     * means the full-period charge applies). One row per allocated slot; a flat with two
     * slots gets two rows, and each is billed separately.
     */
    public static function activeForFlatDuringPeriod(int $flatId, string $periodStart, string $periodEnd): array
    {
        $stmt = db()->prepare(
            'SELECT pa.*, ps.slot_number, ps.slot_type
             FROM parking_allocations pa
             JOIN parking_slots ps ON ps.id = pa.parking_slot_id
             WHERE pa.flat_id = :flat_id
               AND pa.allocated_from <= :period_end
               AND (pa.allocated_to IS NULL OR pa.allocated_to >= :period_start)'
        );
        $stmt->execute(['flat_id' => $flatId, 'period_end' => $periodEnd, 'period_start' => $periodStart]);
        return $stmt->fetchAll();
    }

    /**
     * Allocates a slot, closing any prior open allocation first (a slot can only be
     * actively allocated to one flat/vehicle at a time). $isChargeable is per-allocation,
     * not per-slot — the same slot can be paid for one occupant and free/courtesy for
     * whoever holds it next.
     */
    public static function allocate(int $slotId, int $flatId, ?int $vehicleId, string $fromDate, bool $isChargeable = true): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $existing = self::activeForSlot($slotId);
            if ($existing) {
                $pdo->prepare('UPDATE parking_allocations SET allocated_to = :to WHERE id = :id')
                    ->execute(['to' => $fromDate, 'id' => $existing['id']]);
            }

            $insert = $pdo->prepare(
                'INSERT INTO parking_allocations (parking_slot_id, vehicle_id, flat_id, allocated_from, is_chargeable)
                 VALUES (:slot_id, :vehicle_id, :flat_id, :from_date, :is_chargeable)'
            );
            $insert->execute([
                'slot_id' => $slotId,
                'vehicle_id' => $vehicleId,
                'flat_id' => $flatId,
                'from_date' => $fromDate,
                'is_chargeable' => $isChargeable ? 1 : 0,
            ]);
            $id = (int) $pdo->lastInsertId();

            ParkingSlot::setAllocated($slotId, true);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return $id;
    }

    public static function release(int $allocationId): void
    {
        $pdo = db();

        $stmt = $pdo->prepare('SELECT parking_slot_id FROM parking_allocations WHERE id = :id');
        $stmt->execute(['id' => $allocationId]);
        $row = $stmt->fetch();
        if (!$row) {
            return;
        }

        $pdo->prepare('UPDATE parking_allocations SET allocated_to = CURDATE() WHERE id = :id')
            ->execute(['id' => $allocationId]);

        ParkingSlot::setAllocated((int) $row['parking_slot_id'], false);
    }
}
