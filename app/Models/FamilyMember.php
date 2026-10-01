<?php

declare(strict_types=1);

namespace App\Models;

final class FamilyMember
{
    public static function forMember(int $memberId): array
    {
        $stmt = db()->prepare('SELECT * FROM family_members WHERE member_id = :member_id ORDER BY name');
        $stmt->execute(['member_id' => $memberId]);
        return array_map([self::class, 'withDisplayAge'], $stmt->fetchAll());
    }

    public static function create(int $memberId, string $name, ?string $relation, ?string $dateOfBirth, ?int $age, ?string $phone): int
    {
        $stmt = db()->prepare(
            'INSERT INTO family_members (member_id, name, relation, date_of_birth, age, phone)
             VALUES (:member_id, :name, :relation, :dob, :age, :phone)'
        );
        $stmt->execute([
            'member_id' => $memberId,
            'name' => $name,
            'relation' => $relation,
            'dob' => $dateOfBirth,
            'age' => $dateOfBirth ? null : $age,
            'phone' => $phone,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name, ?string $relation, ?string $dateOfBirth, ?int $age, ?string $phone): void
    {
        $stmt = db()->prepare(
            'UPDATE family_members
             SET name = :name, relation = :relation, date_of_birth = :dob, age = :age, phone = :phone
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'relation' => $relation,
            'dob' => $dateOfBirth,
            'age' => $dateOfBirth ? null : $age,
            'phone' => $phone,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM family_members WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM family_members WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? self::withDisplayAge($row) : null;
    }

    private static function withDisplayAge(array $row): array
    {
        $row['display_age'] = $row['date_of_birth']
            ? self::computeAge($row['date_of_birth'])
            : (isset($row['age']) ? (int) $row['age'] : null);
        return $row;
    }

    private static function computeAge(string $dateOfBirth): int
    {
        $dob = new \DateTimeImmutable($dateOfBirth);
        $today = new \DateTimeImmutable('today');
        return $dob->diff($today)->y;
    }
}
