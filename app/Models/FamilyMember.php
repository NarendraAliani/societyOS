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
            // A stored age only means anything as a fallback when there's no DOB to compute
            // it from live — if DOB is given, age is never trusted from input, always derived.
            'age' => $dateOfBirth ? null : $age,
            'phone' => $phone,
        ]);
        return (int) db()->lastInsertId();
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

    /**
     * Age is never read from a stored column when date_of_birth is known — it's computed
     * fresh from today's date every time a row is fetched, so it can't go stale. The `age`
     * column is only consulted as a fallback when there's no DOB on file.
     */
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
