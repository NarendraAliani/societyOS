<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Expense;
use App\Models\Income;
use App\Models\LedgerEntry;

final class AccountingService
{
    public static function recordIncome(int $societyId, array $fields): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $incomeId = Income::create($societyId, $fields);
            LedgerEntry::create(
                $societyId,
                (int) $fields['account_id'],
                'credit',
                (float) $fields['amount'],
                'income',
                $incomeId,
                $fields['income_date'],
                $fields['category'] . (($fields['description'] ?? '') !== '' ? ' — ' . $fields['description'] : '')
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return $incomeId;
    }

    public static function updateIncome(int $societyId, int $incomeId, array $fields): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            if (!Income::updateForSociety($incomeId, $societyId, $fields)) {
                throw new \\RuntimeException('Income entry was not found or did not change.');
            }
            $ledger = $pdo->prepare(
                'UPDATE ledger_entries
                 SET account_id = :account_id, amount = :amount, entry_date = :entry_date, narration = :narration
                 WHERE society_id = :sid AND reference_type = "income" AND reference_id = :income_id'
            );
            $ledger->execute([
                'account_id' => $fields['account_id'],
                'amount' => $fields['amount'],
                'entry_date' => $fields['income_date'],
                'narration' => $fields['category'] . ($fields['description'] !== '' ? ' — ' . $fields['description'] : ''),
                'sid' => $societyId,
                'income_id' => $incomeId,
            ]);
            $pdo->commit();
        } catch (\\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function deleteIncome(int $societyId, int $incomeId): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $ledger = $pdo->prepare(
                'DELETE FROM ledger_entries
                 WHERE society_id = :sid AND reference_type = "income" AND reference_id = :income_id'
            );
            $ledger->execute(['sid' => $societyId, 'income_id' => $incomeId]);
            if (!Income::deleteForSociety($incomeId, $societyId)) {
                throw new \\RuntimeException('Income entry was not found.');
            }
            $pdo->commit();
        } catch (\\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function recordExpense(int $societyId, array $fields): int
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $expenseId = Expense::create($societyId, $fields);
            LedgerEntry::create(
                $societyId,
                (int) $fields['account_id'],
                'debit',
                (float) $fields['amount'],
                'expense',
                $expenseId,
                $fields['expense_date'],
                $fields['category'] . (($fields['description'] ?? '') !== '' ? ' — ' . $fields['description'] : '')
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return $expenseId;
    }
}
