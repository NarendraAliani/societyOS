<?php

declare(strict_types=1);

namespace App\Models;

final class Member
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT m.*, f.flat_number, fl.floor_number, w.name AS wing_name,
                    1 + (SELECT COUNT(*) FROM family_members fm WHERE fm.member_id = m.id) AS member_count,
                    (SELECT COUNT(*) FROM vehicles v WHERE v.member_id = m.id) AS vehicle_count
             FROM members m
             JOIN flats f ON f.id = m.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE m.society_id = :society_id
             ORDER BY w.name, fl.floor_number, f.flat_number'
        );
        $stmt->execute(['society_id' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT m.*, f.flat_number, fl.floor_number, w.name AS wing_name
             FROM members m
             JOIN flats f ON f.id = m.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE m.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO members (society_id, flat_id, member_type, name, email, phone, alternate_phone, move_in_date, status)
             VALUES (:society_id, :flat_id, :member_type, :name, :email, :phone, :alternate_phone, :move_in_date, :status)'
        );
        $stmt->execute([
            'society_id' => $societyId,
            'flat_id' => $fields['flat_id'],
            'member_type' => $fields['member_type'],
            'name' => $fields['name'],
            'email' => $fields['email'] ?: null,
            'phone' => $fields['phone'],
            'alternate_phone' => $fields['alternate_phone'] ?: null,
            'move_in_date' => $fields['move_in_date'] ?: null,
            'status' => 'active',
        ]);
        $id = (int) db()->lastInsertId();

        Flat::updateOccupancyStatus(
            (int) $fields['flat_id'],
            $fields['member_type'] === 'tenant' ? 'tenant_occupied' : 'owner_occupied'
        );

        return $id;
    }

    public static function update(int $id, array $fields): void
    {
        $stmt = db()->prepare(
            'UPDATE members SET name = :name, email = :email, phone = :phone, alternate_phone = :alternate_phone,
                member_type = :member_type, status = :status
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $fields['name'],
            'email' => $fields['email'] ?: null,
            'phone' => $fields['phone'],
            'alternate_phone' => $fields['alternate_phone'] ?: null,
            'member_type' => $fields['member_type'],
            'status' => $fields['status'],
            'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM members WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
