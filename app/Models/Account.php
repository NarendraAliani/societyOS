<?php

declare(strict_types=1);

namespace App\Models;

final class Account
{
    public static function allForSociety(int $societyId): array
    {
        $stmt = db()->prepare(
            'SELECT a.*,
                (a.opening_balance
                    + COALESCE((SELECT SUM(amount) FROM ledger_entries WHERE account_id = a.id AND entry_type = "credit"), 0)
                    - COALESCE((SELECT SUM(amount) FROM ledger_entries WHERE account_id = a.id AND entry_type = "debit"), 0)
                ) AS current_balance
             FROM accounts a
             WHERE a.society_id = :sid
             ORDER BY a.name'
        );
        $stmt->execute(['sid' => $societyId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM accounts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(int $societyId, string $name, string $type, float $openingBalance): int
    {
        $stmt = db()->prepare(
            'INSERT INTO accounts (society_id, name, account_type, opening_balance) VALUES (:sid, :name, :type, :opening)'
        );
        $stmt->execute(['sid' => $societyId, 'name' => $name, 'type' => $type, 'opening' => $openingBalance]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, string $name, string $type, float $openingBalance): void
    {
        $stmt = db()->prepare(
            'UPDATE accounts SET name = :name, account_type = :type, opening_balance = :opening WHERE id = :id'
        );
        $stmt->execute(['name' => $name, 'type' => $type, 'opening' => $openingBalance, 'id' => $id]);
    }
}
