<?php

declare(strict_types=1);

namespace App\Models;

final class EmergencyContact
{
    public static function forMember(int $memberId): array
    {
        $stmt = db()->prepare('SELECT * FROM emergency_contacts WHERE member_id = :member_id ORDER BY name');
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function create(int $memberId, string $name, ?string $relation, string $phone): int
    {
        $stmt = db()->prepare(
            'INSERT INTO emergency_contacts (member_id, name, relation, phone) VALUES (:member_id, :name, :relation, :phone)'
        );
        $stmt->execute(['member_id' => $memberId, 'name' => $name, 'relation' => $relation, 'phone' => $phone]);
        return (int) db()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM emergency_contacts WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM emergency_contacts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
