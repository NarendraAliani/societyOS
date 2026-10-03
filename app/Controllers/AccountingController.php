<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Income;
use App\Models\Expense;
use App\Models\LedgerEntry;
use App\Models\MaintenanceBill;
use App\Models\Society;
use App\Models\Vendor;
use App\Services\AccountingService;

final class AccountingController
{
    public function accounts(): void
    {
        $pageTitle = 'Accounts';
        $accounts = Account::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/accounting/accounts.php';
    }

    public function storeAccount(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $type = ($_POST['account_type'] ?? '') === 'bank' ? 'bank' : 'cash';
        $opening = $_POST['opening_balance'] ?? '0';

        if ($name === '' || !is_numeric($opening)) {
            Flash::set('error', 'Account name and a numeric opening balance are required.');
        } else {
            Account::create(Society::currentId(), $name, $type, (float) $opening);
            ActivityLog::log('accounting', 'create', "Created account \"{$name}\"");
            Flash::set('success', "Account \"{$name}\" created.");
        }
        header('Location: /accounting/accounts');
        exit;
    }

    public function updateAccount(string $id): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $type = ($_POST['account_type'] ?? '') === 'bank' ? 'bank' : 'cash';
        $opening = $_POST['opening_balance'] ?? '0';

        if ($name === '' || !is_numeric($opening)) {
            Flash::set('error', 'Account name and a numeric opening balance are required.');
        } else {
            Account::update((int) $id, $name, $type, (float) $opening);
            ActivityLog::log('accounting', 'update', "Updated account \"{$name}\" (id {$id})");
            Flash::set('success', 'Account updated.');
        }
        header('Location: /accounting/accounts');
        exit;
    }

    public function income(): void
    {
        $pageTitle = 'Income';
        $entries = Income::allForSociety(Society::currentId());
        $accounts = Account::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/accounting/income.php';
    }

    public function storeIncome(): void
    {
        $this->verifyCsrf();

        $accountId = (int) ($_POST['account_id'] ?? 0);
        $category = trim((string) ($_POST['category'] ?? ''));
        $amount = $_POST['amount'] ?? '';
        $date = $_POST['income_date'] ?? '';

        if ($accountId <= 0 || $category === '' || !is_numeric($amount) || (float) $amount <= 0 || !$date) {
            Flash::set('error', 'Account, category, a positive amount, and date are required.');
            header('Location: /accounting/income');
            exit;
        }

        AccountingService::recordIncome(Society::currentId(), [
            'account_id' => $accountId,
            'category' => $category,
            'amount' => (float) $amount,
            'description' => trim((string) ($_POST['description'] ?? '')),
            'income_date' => $date,
            'created_by' => Auth::id(),
        ]);

        ActivityLog::log('accounting', 'income', "Recorded income of {$amount} ({$category})");
        Flash::set('success', 'Income recorded.');
        header('Location: /accounting/income');
        exit;
    }

    public function expenses(): void
    {
        $pageTitle = 'Expenses';
        $entries = Expense::allForSociety(Society::currentId());
        $accounts = Account::allForSociety(Society::currentId());
        $vendors = Vendor::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/accounting/expenses.php';
    }

    public function storeExpense(): void
    {
        $this->verifyCsrf();

        $accountId = (int) ($_POST['account_id'] ?? 0);
        $category = trim((string) ($_POST['category'] ?? ''));
        $amount = $_POST['amount'] ?? '';
        $date = $_POST['expense_date'] ?? '';

        if ($accountId <= 0 || $category === '' || !is_numeric($amount) || (float) $amount <= 0 || !$date) {
            Flash::set('error', 'Account, category, a positive amount, and date are required.');
            header('Location: /accounting/expenses');
            exit;
        }

        AccountingService::recordExpense(Society::currentId(), [
            'account_id' => $accountId,
            'vendor_id' => (int) ($_POST['vendor_id'] ?? 0) ?: null,
            'category' => $category,
            'amount' => (float) $amount,
            'description' => trim((string) ($_POST['description'] ?? '')),
            'expense_date' => $date,
            'created_by' => Auth::id(),
        ]);

        ActivityLog::log('accounting', 'expense', "Recorded expense of {$amount} ({$category})");
        Flash::set('success', 'Expense recorded.');
        header('Location: /accounting/expenses');
        exit;
    }

    public function vendors(): void
    {
        $pageTitle = 'Vendors';
        $vendors = Vendor::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/accounting/vendors.php';
    }

    public function storeVendor(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        // Set when the vendor is being added from another page (e.g. an asset's "Add AMC
        // Record" form) rather than the main Vendors screen — same pattern as the vehicle
        // create form's return_to_member, so the caller lands back where they started.
        $returnToAsset = is_numeric($_POST['return_to_asset'] ?? '') ? (int) $_POST['return_to_asset'] : null;
        $redirect = $returnToAsset ? "/assets/{$returnToAsset}" : '/accounting/vendors';

        if ($name === '') {
            Flash::set('error', 'Vendor name is required.');
            header("Location: {$redirect}");
            exit;
        }

        $vendorId = Vendor::create(Society::currentId(), [
            'name' => $name,
            'contact_person' => trim((string) ($_POST['contact_person'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'category' => trim((string) ($_POST['category'] ?? '')),
        ]);
        ActivityLog::log('accounting', 'create', "Added vendor \"{$name}\"");
        Flash::set('success', "Vendor \"{$name}\" added.");

        // Pre-select the newly created vendor back on the asset page, so the admin doesn't
        // have to find it again in the dropdown they just left to create it.
        if ($returnToAsset) {
            $redirect .= "?vendor_id={$vendorId}";
        }

        header("Location: {$redirect}");
        exit;
    }

    public function updateVendor(string $id): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Vendor name is required.');
        } else {
            Vendor::update((int) $id, [
                'name' => $name,
                'contact_person' => trim((string) ($_POST['contact_person'] ?? '')),
                'phone' => trim((string) ($_POST['phone'] ?? '')),
                'email' => trim((string) ($_POST['email'] ?? '')),
                'category' => trim((string) ($_POST['category'] ?? '')),
            ]);
            ActivityLog::log('accounting', 'update', "Updated vendor \"{$name}\" (id {$id})");
            Flash::set('success', 'Vendor updated.');
        }
        header('Location: /accounting/vendors');
        exit;
    }

    public function deleteVendor(string $id): void
    {
        $this->verifyCsrf();
        Vendor::delete((int) $id);
        ActivityLog::log('accounting', 'delete', "Removed vendor id {$id}");
        Flash::set('success', 'Vendor removed.');
        header('Location: /accounting/vendors');
        exit;
    }

    public function ledger(): void
    {
        $pageTitle = 'Ledger';
        $accountId = isset($_GET['account_id']) && $_GET['account_id'] !== '' ? (int) $_GET['account_id'] : null;
        $entries = LedgerEntry::allForSociety(Society::currentId(), $accountId);
        $accounts = Account::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/accounting/ledger.php';
    }

    public function cashBook(): void
    {
        $this->renderBook('cash', 'Cash Book', '/accounting/cash-book');
    }

    public function bankBook(): void
    {
        $this->renderBook('bank', 'Bank Book', '/accounting/bank-book');
    }

    /**
     * A traditional cash/bank book: one account at a time (running balance across several
     * accounts summed together wouldn't mean anything), oldest-first, with a running balance
     * column starting from the balance as of the day before the range began.
     */
    private function renderBook(string $accountType, string $pageTitle, string $basePath): void
    {
        $accounts = Account::allForSocietyByType(Society::currentId(), $accountType);

        $accountId = isset($_GET['account_id']) && $_GET['account_id'] !== ''
            ? (int) $_GET['account_id']
            : ($accounts[0]['id'] ?? null);

        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        $account = null;
        $entries = [];
        $openingBalance = 0.0;
        $closingBalance = 0.0;

        if ($accountId !== null) {
            $account = Account::find($accountId);
            $rawEntries = LedgerEntry::forAccountInRange($accountId, $from, $to);
            $openingBalance = Account::balanceBefore($accountId, $from);

            $running = $openingBalance;
            foreach ($rawEntries as $entry) {
                $running += $entry['entry_type'] === 'credit' ? (float) $entry['amount'] : -(float) $entry['amount'];
                $entry['running_balance'] = $running;
                $entries[] = $entry;
            }
            $closingBalance = $running;
        }

        require __DIR__ . '/../Views/accounting/book.php';
    }

    public function reports(): void
    {
        $pageTitle = 'Financial Reports';
        require __DIR__ . '/../Views/accounting/reports_index.php';
    }

    /**
     * Not a true double-entry trial balance — this app only tracks cash/bank accounts, not
     * a full chart of accounts, so it's really an account-balances summary as of a date.
     * Labeled plainly as such in the view rather than implying more accounting rigor than
     * the underlying data supports.
     */
    public function trialBalance(): void
    {
        $pageTitle = 'Trial Balance';
        $asOf = $_GET['as_of'] ?? date('Y-m-d');
        $accounts = Account::allForSociety(Society::currentId());

        $rows = [];
        $total = 0.0;
        foreach ($accounts as $acc) {
            $balance = Account::balanceAsOf((int) $acc['id'], $asOf);
            $rows[] = ['name' => $acc['name'], 'account_type' => $acc['account_type'], 'balance' => $balance];
            $total += $balance;
        }

        require __DIR__ . '/../Views/accounting/trial_balance.php';
    }

    public function incomeExpenseStatement(): void
    {
        $pageTitle = 'Income & Expense Statement';
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-d');

        $incomeByCategory = Income::summaryByCategory(Society::currentId(), $from, $to);
        $expenseByCategory = Expense::summaryByCategory(Society::currentId(), $from, $to);
        $totalIncome = array_sum(array_column($incomeByCategory, 'total'));
        $totalExpense = array_sum(array_column($expenseByCategory, 'total'));
        $net = $totalIncome - $totalExpense;

        require __DIR__ . '/../Views/accounting/income_expense_statement.php';
    }

    /**
     * Assets = cash/bank balances + outstanding maintenance dues (Accounts Receivable).
     * Liabilities are always ₹0 — this schema has no loans/payables table — so Equity
     * (Accumulated Fund) equals total Assets by construction. Labeled plainly in the view;
     * this is a snapshot of today's state, not a historical as-of-date statement, since
     * bill balances aren't tracked with historical snapshots.
     */
    public function balanceSheet(): void
    {
        $pageTitle = 'Balance Sheet';
        $accounts = Account::allForSociety(Society::currentId());
        $accountsTotal = array_sum(array_column($accounts, 'current_balance'));
        $receivables = MaintenanceBill::totalOutstanding(Society::currentId());
        $totalAssets = $accountsTotal + $receivables;

        require __DIR__ . '/../Views/accounting/balance_sheet.php';
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
