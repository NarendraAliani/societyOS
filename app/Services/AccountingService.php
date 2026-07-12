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
