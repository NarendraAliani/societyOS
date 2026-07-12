<?php

declare(strict_types=1);

namespace App\Models;

final class VisitorPass
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT vp.*, f.flat_number, w.name AS wing_name
             FROM visitor_passes vp
             JOIN flats f ON f.id = vp.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE w.society_id = :sid
             ORDER BY vp.valid_from DESC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function findByToken(string $token): ?array
    {
        $stmt = db()->prepare(
            'SELECT vp.*, f.flat_number, w.name AS wing_name
             FROM visitor_passes vp
             JOIN flats f ON f.id = vp.flat_id
             JOIN floors fl ON fl.id = f.floor_id
             JOIN wings w ON w.id = fl.wing_id
             WHERE vp.qr_token = :token'
        );
        $stmt->execute(['token' => $token]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM visitor_passes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $fields): int
    {
        $token = strtoupper(bin2hex(random_bytes(4)));

        $stmt = db()->prepare(
            'INSERT INTO visitor_passes (flat_id, visitor_name, qr_token, valid_from, valid_until, created_by)
             VALUES (:flat_id, :visitor_name, :token, :valid_from, :valid_until, :created_by)'
        );
        $stmt->execute([
            'flat_id' => $fields['flat_id'],
            'visitor_name' => $fields['visitor_name'],
            'token' => $token,
            'valid_from' => $fields['valid_from'],
            'valid_until' => $fields['valid_until'],
            'created_by' => $fields['created_by'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function markUsed(int $id): void
    {
        $stmt = db()->prepare('UPDATE visitor_passes SET used_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
