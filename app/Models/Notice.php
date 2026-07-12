<?php

declare(strict_types=1);

namespace App\Models;

final class Notice
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT n.*, u.name AS published_by_name
             FROM notices n
             LEFT JOIN users u ON u.id = n.published_by
             WHERE n.society_id = :sid
             ORDER BY n.published_at DESC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO notices (society_id, title, body, notice_type, published_by, expires_at)
             VALUES (:sid, :title, :body, :type, :published_by, :expires_at)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'title' => $fields['title'],
            'body' => $fields['body'],
            'type' => $fields['notice_type'],
            'published_by' => $fields['published_by'],
            'expires_at' => $fields['expires_at'] ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM notices WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
