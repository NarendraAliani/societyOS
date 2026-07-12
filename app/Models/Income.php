<?php

declare(strict_types=1);

namespace App\Models;

final class Income
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT i.*, a.name AS account_name
             FROM income i
             JOIN accounts a ON a.id = i.account_id
             WHERE i.society_id = :sid
             ORDER BY i.income_date DESC, i.id DESC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function forDateRange(int $societyId, string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT i.*, a.name AS account_name
             FROM income i
             JOIN accounts a ON a.id = i.account_id
             WHERE i.society_id = :sid AND i.income_date BETWEEN :from AND :to
             ORDER BY i.income_date DESC'
        );
        $stmt->execute(['sid' => $societyId, 'from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO income (society_id, account_id, category, amount, description, income_date, created_by)
             VALUES (:sid, :account_id, :category, :amount, :description, :income_date, :created_by)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'account_id' => $fields['account_id'],
            'category' => $fields['category'],
            'amount' => $fields['amount'],
            'description' => $fields['description'] ?: null,
            'income_date' => $fields['income_date'],
            'created_by' => $fields['created_by'],
        ]);
        return (int) db()->lastInsertId();
    }
}
