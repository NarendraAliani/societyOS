<?php

declare(strict_types=1);

namespace App\Models;

final class ComplaintCategory
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare('SELECT * FROM complaint_categories WHERE society_id = :sid ORDER BY name');
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function create(int $societyId, string $name): int
    {
        $stmt = db()->prepare('INSERT INTO complaint_categories (society_id, name) VALUES (:sid, :name)');
        $stmt->execute(['sid' => $societyId, 'name' => $name]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name): void
    {
        $stmt = db()->prepare('UPDATE complaint_categories SET name = :name WHERE id = :id');
        $stmt->execute(['name' => $name, 'id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM complaint_categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
