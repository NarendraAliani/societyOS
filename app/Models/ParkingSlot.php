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
             WHERE ps.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
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
        $stmt = db()->prepare('UPDATE parking_slots SET slot_number = :slot_number, slot_type = :slot_type WHERE id = :id');
        $stmt->execute(['slot_number' => $slotNumber, 'slot_type' => $slotType, 'id' => $id]);
    }

    public static function setAllocated(int $id, bool $allocated): void
    {
        $stmt = db()->prepare('UPDATE parking_slots SET is_allocated = :allocated WHERE id = :id');
        $stmt->execute(['allocated' => $allocated ? 1 : 0, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM parking_slots WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
