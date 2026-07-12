<?php

declare(strict_types=1);

namespace App\Models;

final class Vendor
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare('SELECT * FROM vendors WHERE society_id = :sid ORDER BY name');
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO vendors (society_id, name, contact_person, phone, email, category)
             VALUES (:sid, :name, :contact_person, :phone, :email, :category)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'name' => $fields['name'],
            'contact_person' => $fields['contact_person'] ?: null,
            'phone' => $fields['phone'] ?: null,
            'email' => $fields['email'] ?: null,
            'category' => $fields['category'] ?: null,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $fields): void
    {
        $stmt = db()->prepare(
            'UPDATE vendors SET name = :name, contact_person = :contact_person, phone = :phone, email = :email, category = :category
             WHERE id = :id'
        );
        $stmt->execute([
            'name' => $fields['name'],
            'contact_person' => $fields['contact_person'] ?: null,
            'phone' => $fields['phone'] ?: null,
            'email' => $fields['email'] ?: null,
            'category' => $fields['category'] ?: null,
            'id' => $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM vendors WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
