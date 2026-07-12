<?php

declare(strict_types=1);

namespace App\Models;

final class Visitor
{
    public static function allForSociety(int $societyId, ?string $date = null): array
    {
        $sql = 'SELECT v.*, f.flat_number, w.name AS wing_name
                FROM visitors v
                JOIN flats f ON f.id = v.flat_id
                JOIN floors fl ON fl.id = f.floor_id
                JOIN wings w ON w.id = fl.wing_id
                WHERE v.society_id = :sid';
        $params = ['sid' => $societyId];

        if ($date !== null) {
            $sql .= ' AND DATE(v.check_in_at) = :date';
            $params['date'] = $date;
        }

        $sql .= ' ORDER BY v.check_in_at DESC';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function forDateRange(int $societyId, string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT v.*, f.flat_number, w.name AS wing_name
             FROM visitors v
             JOIN flats f ON f.id = v.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE v.society_id = :sid AND DATE(v.check_in_at) BETWEEN :from AND :to
             ORDER BY v.check_in_at DESC'
        );
        $stmt->execute(['sid' => $societyId, 'from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM visitors WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO visitors (society_id, flat_id, name, phone, purpose, approval_status, logged_by)
             VALUES (:sid, :flat_id, :name, :phone, :purpose, :approval_status, :logged_by)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'flat_id' => $fields['flat_id'],
            'name' => $fields['name'],
            'phone' => $fields['phone'] ?: null,
            'purpose' => $fields['purpose'] ?: null,
            'approval_status' => $fields['approval_status'] ?? 'pending',
            'logged_by' => $fields['logged_by'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function setApprovalStatus(int $id, string $status, ?int $approvedByMemberId): void
    {
        $stmt = db()->prepare('UPDATE visitors SET approval_status = :status, approved_by_member_id = :member_id WHERE id = :id');
        $stmt->execute(['status' => $status, 'member_id' => $approvedByMemberId, 'id' => $id]);
    }

    public static function checkOut(int $id): void
    {
        $stmt = db()->prepare('UPDATE visitors SET check_out_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
