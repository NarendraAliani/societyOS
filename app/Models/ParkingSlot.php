<?php

declare(strict_types=1);

namespace App\Models;

final class ParkingSlot
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT ps.*,
                    pa.id AS allocation_id, pa.flat_id, pa.is_chargeable, f.flat_number, w.name AS wing_name,
                    v.registration_number,
                    (SELECT amount FROM parking_rates pr
                     WHERE pr.society_id = ps.society_id AND pr.slot_type = ps.slot_type AND pr.effective_from <= CURDATE()
                     ORDER BY pr.effective_from DESC LIMIT 1) AS current_rate
             FROM parking_slots ps
             LEFT JOIN parking_allocations pa ON pa.parking_slot_id = ps.id AND pa.allocated_to IS NULL
             LEFT JOIN flats f ON f.id = pa.flat_id
             LEFT JOIN floors fl ON fl.id = f.floor_id
             LEFT JOIN wings w ON w.id = fl.wing_id
             LEFT JOIN vehicles v ON v.id = pa.vehicle_id
             WHERE ps.society_id = :sid
             ORDER BY ps.slot_number'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT ps.*,
                    (SELECT amount FROM parking_rates pr
                     WHERE pr.society_id = ps.society_id AND pr.slot_type = ps.slot_type AND pr.effective_from <= CURDATE()
                     ORDER BY pr.effective_from DESC LIMIT 1) AS current_rate
             FROM parking_slots ps
             WHERE ps.id = :id AND ps.society_id = :sid'
        );
        $stmt->execute(['id' => $id, 'sid' => Society::currentId()]);
        return $stmt->fetch() ?: null;
    }

    private const COMMON_TWO_WHEELER_PREFIX = 'COMMON-2W-';

    public static function commonTwoWheelerStats(int $societyId): array
    {
        $stmt = db()->prepare(
            "SELECT COUNT(*) AS capacity,
                    SUM(CASE WHEN EXISTS (
                        SELECT 1 FROM parking_allocations pa
                        WHERE pa.parking_slot_id = ps.id AND pa.allocated_to IS NULL
                    ) THEN 1 ELSE 0 END) AS occupied
             FROM parking_slots ps
             WHERE ps.society_id = :sid AND ps.slot_number LIKE 'COMMON-2W-%'"
        );
        $stmt->execute(['sid' => $societyId]);
        $row = $stmt->fetch() ?: [];
        return ['capacity' => (int) ($row['capacity'] ?? 0), 'occupied' => (int) ($row['occupied'] ?? 0)];
    }

    /**
     * Resize the shared two-wheeler parking area using ordinary parking-slot records,
     * so existing allocation, billing and history features continue to work unchanged.
     */
    public static function resizeCommonTwoWheeler(int $societyId, int $capacity): array
    {
        if ($capacity < 0 || $capacity > 500) {
            throw new \\InvalidArgumentException('Common parking capacity must be between 0 and 500.');
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "SELECT ps.id, ps.slot_number,
                        EXISTS (SELECT 1 FROM parking_allocations pa
                                WHERE pa.parking_slot_id = ps.id AND pa.allocated_to IS NULL) AS occupied
                 FROM parking_slots ps
                 WHERE ps.society_id = :sid AND ps.slot_number LIKE 'COMMON-2W-%'
                 ORDER BY ps.slot_number DESC FOR UPDATE"
            );
            $stmt->execute(['sid' => $societyId]);
            $slots = $stmt->fetchAll();
            $current = count($slots);
            $occupied = count(array_filter($slots, static fn (array $slot): bool => (bool) $slot['occupied']));

            if ($capacity < $occupied) {
                throw new \\DomainException("Capacity cannot be lower than the {$occupied} currently occupied common parking spaces.");
            }

            if ($capacity < $current) {
                $toRemove = $current - $capacity;
                foreach ($slots as $slot) {
                    if ($toRemove === 0) {
                        break;
                    }
                    if ((bool) $slot['occupied']) {
                        continue;
                    }
                    $delete = $pdo->prepare('DELETE FROM parking_slots WHERE id = :id AND society_id = :sid');
                    $delete->execute(['id' => $slot['id'], 'sid' => $societyId]);
                    $toRemove--;
                }
                if ($toRemove !== 0) {
                    throw new \\DomainException('Unable to reduce capacity because some spaces have parking history.');
                }
            } elseif ($capacity > $current) {
                $next = 1;
                $existingNumbers = [];
                foreach ($slots as $slot) {
                    $existingNumbers[$slot['slot_number']] = true;
                }
                while (count($existingNumbers) < $capacity) {
                    $slotNumber = self::COMMON_TWO_WHEELER_PREFIX . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
                    if (!isset($existingNumbers[$slotNumber])) {
                        $insert = $pdo->prepare(
                            "INSERT INTO parking_slots (society_id, slot_number, slot_type) VALUES (:sid, :number, 'two_wheeler')"
                        );
                        $insert->execute(['sid' => $societyId, 'number' => $slotNumber]);
                        $existingNumbers[$slotNumber] = true;
                    }
                    $next++;
                }
            }

            $pdo->commit();
            return self::commonTwoWheelerStats($societyId);
        } catch (\\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function create(int $societyId, string $slotNumber, string $slotType): int
    {
        $stmt = db()->prepare(
            'INSERT INTO parking_slots (society_id, slot_number, slot_type) VALUES (:sid, :slot_number, :slot_type)'
        );
        $stmt->execute(['sid' => $societyId, 'slot_number' => $slotNumber, 'slot_type' => $slotType]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $slotNumber, string $slotType): void
    {
        $stmt = db()->prepare('UPDATE parking_slots SET slot_number = :slot_number, slot_type = :slot_type WHERE id = :id AND society_id = :sid');
        $stmt->execute(['slot_number' => $slotNumber, 'slot_type' => $slotType, 'id' => $id, 'sid' => Society::currentId()]);
    }

    public static function setAllocated(int $id, bool $allocated): void
    {
        $stmt = db()->prepare('UPDATE parking_slots SET is_allocated = :allocated WHERE id = :id');
        $stmt->execute(['allocated' => $allocated ? 1 : 0, 'id' => $id, 'sid' => Society::currentId()]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM parking_slots WHERE id = :id');
        $stmt->execute(['id' => $id, 'sid' => Society::currentId()]);
    }
}
