<?php

declare(strict_types=1);

namespace App\Models;

final class Asset
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT a.*, ac.name AS category_name
             FROM assets a
             JOIN asset_categories ac ON ac.id = a.category_id
             WHERE a.society_id = :sid
             ORDER BY a.name'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT a.*, ac.name AS category_name
             FROM assets a
             JOIN asset_categories ac ON ac.id = a.category_id
             WHERE a.id = :id'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO assets (society_id, category_id, name, purchase_date, purchase_cost, warranty_expiry, location, status)
             VALUES (:sid, :category_id, :name, :purchase_date, :purchase_cost, :warranty_expiry, :location, "active")'
        );
        $stmt->execute([
            'sid' => $societyId,
            'category_id' => $fields['category_id'],
            'name' => $fields['name'],
            'purchase_date' => $fields['purchase_date'] ?: null,
            'purchase_cost' => $fields['purchase_cost'] !== '' ? $fields['purchase_cost'] : null,
            'warranty_expiry' => $fields['warranty_expiry'] ?: null,
            'location' => $fields['location'] ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $fields): void
    {
        $stmt = db()->prepare(
            'UPDATE assets SET category_id = :category_id, name = :name, purchase_date = :purchase_date,
                purchase_cost = :purchase_cost, warranty_expiry = :warranty_expiry, location = :location
             WHERE id = :id'
        );
        $stmt->execute([
            'category_id' => $fields['category_id'],
            'name' => $fields['name'],
            'purchase_date' => $fields['purchase_date'] ?: null,
            'purchase_cost' => $fields['purchase_cost'] !== '' ? $fields['purchase_cost'] : null,
            'warranty_expiry' => $fields['warranty_expiry'] ?: null,
            'location' => $fields['location'] ?: null,
            'id' => $id,
        ]);
    }

    public static function setStatus(int $id, string $status): void
    {
        $stmt = db()->prepare('UPDATE assets SET status = :status WHERE id = :id');
        $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
