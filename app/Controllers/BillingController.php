<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\FinancialYear;
use App\Models\MaintenanceBill;
use App\Models\Receipt;
use App\Models\Society;
use App\Services\BillingService;
use Dompdf\Dompdf;
use Dompdf\Options;

final class BillingController
{
    public function index(): void
    {
        $pageTitle = 'Maintenance Bills';
        $status = $_GET['status'] ?? '';
        $bills = MaintenanceBill::allForSociety(Society::currentId(), $status ?: null);
        require __DIR__ . '/../Views/billing/index.php';
    }

    public function showGenerateForm(): void
    {
        $pageTitle = 'Generate Bills';
        $financialYears = FinancialYear::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/billing/generate.php';
    }

    public function generate(): void
    {
        $this->verifyCsrf();

        $financialYearId = (int) ($_POST['financial_year_id'] ?? 0);
        $periodStart = $_POST['period_start'] ?? '';
        $periodEnd = $_POST['period_end'] ?? '';
        $dueDate = $_POST['due_date'] ?? '';

        if ($financialYearId <= 0 || !$periodStart || !$periodEnd || !$dueDate) {
            Flash::set('error', 'All fields are required to generate bills.');
            header('Location: /billing/generate');
            exit;
        }

        $result = BillingService::generateForPeriod(Society::currentId(), $financialYearId, $periodStart, $periodEnd, $dueDate);

        ActivityLog::log('billing', 'generate', "Generated bills for {$periodStart} to {$periodEnd}: {$result['created']} created, {$result['skipped']} skipped, {$result['noCharge']} no-charge");
        Flash::set('success', "{$result['created']} bill(s) generated, {$result['skipped']} skipped (already billed for this period), {$result['noCharge']} had nothing to charge.");
        header('Location: /billing');
        exit;
    }

    public function show(string $id): void
    {
        $pageTitle = 'Bill Detail';
        $bill = MaintenanceBill::find((int) $id);
        if (!$bill) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $items = MaintenanceBill::items((int) $id);
        $payments = MaintenanceBill::payments((int) $id);
        require __DIR__ . '/../Views/billing/show.php';
    }

    public function recordPayment(string $id): void
    {
        $this->verifyCsrf();

        $amount = $_POST['amount'] ?? '';
        $mode = $_POST['payment_mode'] ?? '';
        $validModes = ['cash', 'cheque', 'upi', 'bank_transfer', 'card'];

        if (!is_numeric($amount) || (float) $amount <= 0 || !in_array($mode, $validModes, true)) {
            Flash::set('error', 'A valid amount and payment mode are required.');
            header("Location: /billing/{$id}");
            exit;
        }

        $result = BillingService::recordPayment(
            (int) $id,
            (float) $amount,
            $mode,
            trim((string) ($_POST['reference_number'] ?? '')) ?: null,
            Auth::id()
        );

        ActivityLog::log('billing', 'payment', "Recorded payment of {$amount} on bill id {$id}, receipt {$result['receipt_number']}");
        Flash::set('success', "Payment recorded. Receipt {$result['receipt_number']} issued.");
        header("Location: /billing/{$id}");
        exit;
    }

    public function downloadReceipt(string $paymentId): void
    {
        $receipt = Receipt::findByPayment((int) $paymentId);
        if (!$receipt) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $detail = Receipt::detail((int) $receipt['id']);
        if (!$detail || (int) $detail['society_id'] !== Society::currentId()) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $society = Society::current();

        ob_start();
        require __DIR__ . '/../Views/billing/receipt_pdf.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'portrait');
        $dompdf->render();

        $dompdf->stream($detail['receipt_number'] . '.pdf', ['Attachment' => false]);
        exit;
    }

    public function defaulters(): void
    {
        $pageTitle = 'Defaulter Report';
        $defaulters = MaintenanceBill::defaulters(Society::currentId());
        require __DIR__ . '/../Views/billing/defaulters.php';
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
