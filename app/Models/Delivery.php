<?php

declare(strict_types=1);

namespace App\Models;

final class Delivery
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT d.*, f.flat_number, w.name AS wing_name
             FROM deliveries d
             JOIN flats f ON f.id = d.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE d.society_id = :sid
             ORDER BY d.received_at DESC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO deliveries (society_id, flat_id, courier_company, recipient_name, logged_by)
             VALUES (:sid, :flat_id, :courier_company, :recipient_name, :logged_by)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'flat_id' => $fields['flat_id'],
            'courier_company' => $fields['courier_company'] ?: null,
            'recipient_name' => $fields['recipient_name'] ?: null,
            'logged_by' => $fields['logged_by'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function markCollected(int $id): void
    {
        $stmt = db()->prepare('UPDATE deliveries SET collected_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
