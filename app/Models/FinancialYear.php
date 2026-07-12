<?php

declare(strict_types=1);

namespace App\Models;

final class FinancialYear
{
    public static function current(int $societyId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM financial_years WHERE society_id = :sid AND is_current = 1 LIMIT 1');
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetch() ?: null;
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM financial_years WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare('SELECT * FROM financial_years WHERE society_id = :sid ORDER BY start_date DESC');
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }
}
