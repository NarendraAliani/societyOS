<?php

declare(strict_types=1);

namespace App\Models;

final class Document
{
    public static function forMember(int $memberId): array
    {
        $stmt = db()->prepare(
            'SELECT d.*, u.name AS uploaded_by_name
             FROM documents d
             LEFT JOIN users u ON u.id = d.uploaded_by
             WHERE d.member_id = :member_id
             ORDER BY d.created_at DESC'
        );
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM documents WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, int $memberId, string $title, string $filePath, ?string $fileType, ?int $uploadedBy): int
    {
        $stmt = db()->prepare(
            'INSERT INTO documents (society_id, member_id, title, file_path, file_type, uploaded_by)
             VALUES (:society_id, :member_id, :title, :file_path, :file_type, :uploaded_by)'
        );
        $stmt->execute([
            'society_id' => $societyId,
            'member_id' => $memberId,
            'title' => $title,
            'file_path' => $filePath,
            'file_type' => $fileType,
            'uploaded_by' => $uploadedBy,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM documents WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
