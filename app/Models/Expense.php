<?php

declare(strict_types=1);

namespace App\Models;

final class Expense
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT e.*, a.name AS account_name, v.name AS vendor_name
             FROM expenses e
             JOIN accounts a ON a.id = e.account_id
             LEFT JOIN vendors v ON v.id = e.vendor_id
             WHERE e.society_id = :sid
             ORDER BY e.expense_date DESC, e.id DESC'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function forDateRange(int $societyId, string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT e.*, a.name AS account_name, v.name AS vendor_name
             FROM expenses e
             JOIN accounts a ON a.id = e.account_id
             LEFT JOIN vendors v ON v.id = e.vendor_id
             WHERE e.society_id = :sid AND e.expense_date BETWEEN :from AND :to
             ORDER BY e.expense_date DESC'
        );
        $stmt->execute(['sid' => $societyId, 'from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    public static function create(int $societyId, array $fields): int
    {
        $stmt = db()->prepare(
            'INSERT INTO expenses (society_id, account_id, vendor_id, category, amount, description, expense_date, created_by)
             VALUES (:sid, :account_id, :vendor_id, :category, :amount, :description, :expense_date, :created_by)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'account_id' => $fields['account_id'],
            'vendor_id' => $fields['vendor_id'] ?: null,
            'category' => $fields['category'],
            'amount' => $fields['amount'],
            'description' => $fields['description'] ?: null,
            'expense_date' => $fields['expense_date'],
            'created_by' => $fields['created_by'],
        ]);
        return (int) db()->lastInsertId();
    }
}
