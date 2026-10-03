<?php

declare(strict_types=1);

namespace App\Models;

final class MaintenanceHeadRate
{
    public static function forHead(int $headId): array
    {
        $stmt = db()->prepare(
            'SELECT r.*, u.name AS created_by_name
             FROM maintenance_head_rates r
             LEFT JOIN users u ON u.id = r.created_by
             WHERE r.maintenance_head_id = :head_id
             ORDER BY r.effective_from DESC'
        );
        $stmt->execute(['head_id' => $headId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM maintenance_head_rates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * The amount effective as of a given date (defaults to today) — the rate with the
     * latest effective_from that is not after that date. Returns null if no rate has
     * taken effect yet by that date.
     */
    public static function amountAsOf(int $headId, ?string $date = null): ?float
    {
        $stmt = db()->prepare(
            'SELECT amount FROM maintenance_head_rates
             WHERE maintenance_head_id = :head_id AND effective_from <= :date
             ORDER BY effective_from DESC LIMIT 1'
        );
        $stmt->execute(['head_id' => $headId, 'date' => $date ?? date('Y-m-d')]);
        $amount = $stmt->fetchColumn();
        return $amount !== false ? (float) $amount : null;
    }

    public static function create(int $headId, float $amount, string $effectiveFrom, ?int $createdBy): int
    {
        $stmt = db()->prepare(
            'INSERT INTO maintenance_head_rates (maintenance_head_id, amount, effective_from, created_by)
             VALUES (:head_id, :amount, :effective_from, :created_by)'
        );
        $stmt->execute(['head_id' => $headId, 'amount' => $amount, 'effective_from' => $effectiveFrom, 'created_by' => $createdBy]);
        return (int) db()->lastInsertId();
    }

    /**
     * Deletes a scheduled rate only if it hasn't taken effect yet. Rates already in effect
     * are append-only — see the schema comment on maintenance_head_rates for why.
     */
    public static function deleteIfFuture(int $id): bool
    {
        $rate = self::find($id);
        if (!$rate || strtotime((string) $rate['effective_from']) <= strtotime('today')) {
            return false;
        }

        $stmt = db()->prepare('DELETE FROM maintenance_head_rates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return true;
    }
}
