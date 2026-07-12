<?php

declare(strict_types=1);

namespace App\Models;

final class Floor
{
    public static function forWingWithCounts(int $wingId): array
    {
        $stmt = db()->prepare(
            'SELECT fl.*, COUNT(ft.id) AS flat_count
             FROM floors fl
             LEFT JOIN flats ft ON ft.floor_id = fl.id
             WHERE fl.wing_id = :wing_id
             GROUP BY fl.id
             ORDER BY fl.floor_number'
        );
        $stmt->execute(['wing_id' => $wingId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM floors WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $wingId, int $floorNumber): int
    {
        $stmt = db()->prepare('INSERT INTO floors (wing_id, floor_number) VALUES (:wing_id, :floor_number)');
        $stmt->execute(['wing_id' => $wingId, 'floor_number' => $floorNumber]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, int $floorNumber): void
    {
        $stmt = db()->prepare('UPDATE floors SET floor_number = :floor_number WHERE id = :id');
        $stmt->execute(['floor_number' => $floorNumber, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM floors WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
