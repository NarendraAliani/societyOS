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

    /** Category totals for a date range, for the Income & Expense Statement. */
    public static function summaryByCategory(int $societyId, string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT category, SUM(amount) AS total
             FROM expenses
             WHERE society_id = :sid AND expense_date BETWEEN :from AND :to
             GROUP BY category
             ORDER BY total DESC'
        );
        $stmt->execute(['sid' => $societyId, 'from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    /**
     * Return society-specific configured categories plus any legacy categories already
     * used by expense entries, so existing records remain selectable.
     */
    public static function categoriesForSociety(int $societyId): array
    {
        $configured = json_decode((string) Settings::get($societyId, 'expense_categories', '[]'), true);
        if (!is_array($configured)) {
            $configured = [];
        }

        $stmt = db()->prepare(
            'SELECT DISTINCT category FROM expenses
             WHERE society_id = :sid AND TRIM(category) <> \'\'
             ORDER BY category'
        );
        $stmt->execute(['sid' => $societyId]);
        $existing = array_column($stmt->fetchAll(), 'category');
        $names = [];
        foreach (array_merge($configured, $existing) as $name) {
            if (!is_string($name)) {
                continue;
            }
            $name = trim($name);
            if ($name === '') {
                continue;
            }
            $names[mb_strtolower($name)] = $name;
        }
        $names = array_values($names);
        natcasesort($names);
        return array_values($names);
    }

    public static function addCategory(int $societyId, string $name): bool
    {
        $name = trim($name);
        if ($name === '' || mb_strlen($name) > 100) {
            return false;
        }

        $categories = self::categoriesForSociety($societyId);
        foreach ($categories as $category) {
            if (mb_strtolower($category) === mb_strtolower($name)) {
                return false;
            }
        }

        $categories[] = $name;
        natcasesort($categories);
        Settings::set($societyId, 'expense_categories', json_encode(array_values($categories), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return true;
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
