<?php

declare(strict_types=1);

namespace App\Models;

final class LedgerEntry
{
    public static function create(
        int $societyId,
        int $accountId,
        string $entryType,
        float $amount,
        string $referenceType,
        int $referenceId,
        string $entryDate,
        ?string $narration
    ): int {
        $stmt = db()->prepare(
            'INSERT INTO ledger_entries (society_id, account_id, entry_type, amount, reference_type, reference_id, entry_date, narration)
             VALUES (:sid, :account_id, :entry_type, :amount, :reference_type, :reference_id, :entry_date, :narration)'
        );
        $stmt->execute([
            'sid' => $societyId,
            'account_id' => $accountId,
            'entry_type' => $entryType,
            'amount' => $amount,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'entry_date' => $entryDate,
            'narration' => $narration,
        ]);
        return (int) db()->lastInsertId();
    }

    public static function forAccount(int $accountId): array
    {
        $stmt = db()->prepare('SELECT * FROM ledger_entries WHERE account_id = :account_id ORDER BY entry_date, id');
        $stmt->execute(['account_id' => $accountId]);
        return $stmt->fetchAll();
    }

    /** Oldest-first, for building a running balance (cash/bank book), scoped to a date range. */
    public static function forAccountInRange(int $accountId, string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT * FROM ledger_entries
             WHERE account_id = :account_id AND entry_date BETWEEN :from AND :to
             ORDER BY entry_date, id'
        );
        $stmt->execute(['account_id' => $accountId, 'from' => $from, 'to' => $to]);
        return $stmt->fetchAll();
    }

    public static function allForSociety(int $societyId, ?int $accountId = null): array
    {
        $sql = 'SELECT le.*, a.name AS account_name
                FROM ledger_entries le
                JOIN accounts a ON a.id = le.account_id
                WHERE le.society_id = :sid';
        $params = ['sid' => $societyId];

        if ($accountId !== null) {
            $sql .= ' AND le.account_id = :account_id';
            $params['account_id'] = $accountId;
        }

        $sql .= ' ORDER BY le.entry_date DESC, le.id DESC';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
