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

    public static function allForSocietyByType(int $societyId, string $type): array
    {
        $stmt = db()->prepare('SELECT * FROM accounts WHERE society_id = :sid AND account_type = :type ORDER BY name');
        $stmt->execute(['sid' => $societyId, 'type' => $type]);
        return $stmt->fetchAll();
    }

    /** Balance as of the start of $date — opening_balance plus all entries strictly before it. */
    public static function balanceBefore(int $accountId, string $date): float
    {
        $account = self::find($accountId);
        if (!$account) {
            return 0.0;
        }

        $stmt = db()->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN entry_type = "credit" THEN amount ELSE 0 END), 0)
                - COALESCE(SUM(CASE WHEN entry_type = "debit" THEN amount ELSE 0 END), 0) AS net
             FROM ledger_entries WHERE account_id = :account_id AND entry_date < :date'
        );
        $stmt->execute(['account_id' => $accountId, 'date' => $date]);
        $net = (float) $stmt->fetchColumn();

        return (float) $account['opening_balance'] + $net;
    }

    /** Balance including everything posted on or before $date — for the Trial Balance. */
    public static function balanceAsOf(int $accountId, string $date): float
    {
        $account = self::find($accountId);
        if (!$account) {
            return 0.0;
        }

        $stmt = db()->prepare(
            'SELECT
                COALESCE(SUM(CASE WHEN entry_type = "credit" THEN amount ELSE 0 END), 0)
                - COALESCE(SUM(CASE WHEN entry_type = "debit" THEN amount ELSE 0 END), 0) AS net
             FROM ledger_entries WHERE account_id = :account_id AND entry_date <= :date'
        );
        $stmt->execute(['account_id' => $accountId, 'date' => $date]);
        $net = (float) $stmt->fetchColumn();

        return (float) $account['opening_balance'] + $net;
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
