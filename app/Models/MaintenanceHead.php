<?php

declare(strict_types=1);

namespace App\Models;

final class MaintenanceHead
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT mh.*,
                    (SELECT amount FROM maintenance_head_rates r
                     WHERE r.maintenance_head_id = mh.id AND r.effective_from <= CURDATE()
                     ORDER BY r.effective_from DESC LIMIT 1) AS current_amount,
                    (SELECT COUNT(*) FROM maintenance_head_rates r
                     WHERE r.maintenance_head_id = mh.id AND r.effective_from > CURDATE()) AS scheduled_count
             FROM maintenance_heads mh
             WHERE mh.society_id = :society_id
             ORDER BY mh.name'
        );
        $stmt->execute(['society_id' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM maintenance_heads WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Creates the head and its first rate (effective today) in one transaction — a head
     * never exists without at least one rate.
     */
    public static function create(int $societyId, string $name, string $calculationType, float $initialAmount, ?int $createdBy): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO maintenance_heads (society_id, name, calculation_type) VALUES (:society_id, :name, :calculation_type)'
            );
            $stmt->execute(['society_id' => $societyId, 'name' => $name, 'calculation_type' => $calculationType]);
            $headId = (int) $pdo->lastInsertId();

            MaintenanceHeadRate::create($headId, $initialAmount, date('Y-m-d'), $createdBy);

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return $headId;
    }

    public static function update(int $id, string $name, string $calculationType): void
    {
        $stmt = db()->prepare('UPDATE maintenance_heads SET name = :name, calculation_type = :calculation_type WHERE id = :id');
        $stmt->execute(['name' => $name, 'calculation_type' => $calculationType, 'id' => $id]);
    }

    public static function toggleActive(int $id): void
    {
        $stmt = db()->prepare('UPDATE maintenance_heads SET is_active = NOT is_active WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM maintenance_heads WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
