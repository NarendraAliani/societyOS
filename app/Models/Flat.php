<?php

declare(strict_types=1);

namespace App\Models;

final class Flat
{
    public static function forFloor(int $floorId): array
    {
        $stmt = db()->prepare('SELECT * FROM flats WHERE floor_id = :floor_id ORDER BY flat_number');
        $stmt->execute(['floor_id' => $floorId]);
        return $stmt->fetchAll();
    }

    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT f.*, fl.floor_number, w.name AS wing_name
             FROM flats f
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE w.society_id = :society_id
             ORDER BY w.name, fl.floor_number, f.flat_number'
        );
        $stmt->execute(['society_id' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT f.*, fl.floor_number, fl.wing_id, w.name AS wing_name
             FROM flats f
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE f.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $floorId, string $flatNumber, ?string $flatType, ?float $carpetArea): int
    {
        $stmt = db()->prepare(
            'INSERT INTO flats (floor_id, flat_number, flat_type, carpet_area_sqft)
             VALUES (:floor_id, :flat_number, :flat_type, :carpet_area)'
        );
        $stmt->execute([
            'floor_id' => $floorId,
            'flat_number' => $flatNumber,
            'flat_type' => $flatType,
            'carpet_area' => $carpetArea,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $flatNumber, ?string $flatType, ?float $carpetArea): void
    {
        $stmt = db()->prepare(
            'UPDATE flats SET flat_number = :flat_number, flat_type = :flat_type, carpet_area_sqft = :carpet_area WHERE id = :id'
        );
        $stmt->execute(['flat_number' => $flatNumber, 'flat_type' => $flatType, 'carpet_area' => $carpetArea, 'id' => $id]);
    }

    public static function updateOccupancyStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE flats SET occupancy_status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM flats WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
