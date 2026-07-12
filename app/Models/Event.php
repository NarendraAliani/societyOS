<?php

declare(strict_types=1);

namespace App\Models;

final class Event
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare('SELECT * FROM events WHERE society_id = :sid ORDER BY starts_at DESC');
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO events (society_id, title, description, venue, starts_at, ends_at, created_by)
             VALUES (:sid, :title, :description, :venue, :starts_at, :ends_at, :created_by)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'title' => $fields['title'],
            'description' => $fields['description'] ?: null,
            'venue' => $fields['venue'] ?: null,
            'starts_at' => $fields['starts_at'],
            'ends_at' => $fields['ends_at'] ?: null,
            'created_by' => $fields['created_by'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM events WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
