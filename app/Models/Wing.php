<?php

declare(strict_types=1);

namespace App\Models;

final class Wing
{
    public static function allWithCounts(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT w.*,
                    COUNT(DISTINCT fl.id) AS floor_count,
                    COUNT(DISTINCT ft.id) AS flat_count
             FROM wings w
             LEFT JOIN floors fl ON fl.wing_id = w.id
             LEFT JOIN flats ft ON ft.floor_id = fl.id
             WHERE w.society_id = :society_id
             GROUP BY w.id
             ORDER BY w.name'
        );
        $stmt->execute(['society_id' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM wings WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, string $name): int
    {
        $stmt = db()->prepare('INSERT INTO wings (society_id, name) VALUES (:society_id, :name)');
        $stmt->execute(['society_id' => $societyId, 'name' => $name]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name): void
    {
        $stmt = db()->prepare('UPDATE wings SET name = :name WHERE id = :id');
        $stmt->execute(['name' => $name, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM wings WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
