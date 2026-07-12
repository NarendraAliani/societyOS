<?php

declare(strict_types=1);

namespace App\Models;

final class Vehicle
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT v.*, m.name AS member_name, f.flat_number, w.name AS wing_name
             FROM vehicles v
             JOIN members m ON m.id = v.member_id
             JOIN flats f ON f.id = m.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE m.society_id = :sid
             ORDER BY w.name, f.flat_number'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function forMember(int $memberId): array
    {
        $stmt = db()->prepare('SELECT * FROM vehicles WHERE member_id = :member_id ORDER BY registration_number');
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT v.*, m.name AS member_name, f.flat_number, w.name AS wing_name
             FROM vehicles v
             JOIN members m ON m.id = v.member_id
             JOIN flats f ON f.id = m.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE v.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO vehicles (member_id, vehicle_type, registration_number, make, model, color)
             VALUES (:member_id, :vehicle_type, :registration_number, :make, :model, :color)'
        );
        $stmt->execute([
            'member_id' => $fields['member_id'],
            'vehicle_type' => $fields['vehicle_type'],
            'registration_number' => $fields['registration_number'],
            'make' => $fields['make'] ?: null,
            'model' => $fields['model'] ?: null,
            'color' => $fields['color'] ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $fields): void
    {
        $stmt = db()->prepare(
            'UPDATE vehicles SET vehicle_type = :vehicle_type, registration_number = :registration_number,
                make = :make, model = :model, color = :color
             WHERE id = :id'
        );
        $stmt->execute([
            'vehicle_type' => $fields['vehicle_type'],
            'registration_number' => $fields['registration_number'],
            'make' => $fields['make'] ?: null,
            'model' => $fields['model'] ?: null,
            'color' => $fields['color'] ?: null,
            'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM vehicles WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function registrationExists(string $registrationNumber, ?int $excludingId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM vehicles WHERE registration_number = :reg';
        $params = ['reg' => $registrationNumber];

        if ($excludingId !== null) {
            $sql .= ' AND id != :excluding_id';
            $params['excluding_id'] = $excludingId;
        }

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    }
}
