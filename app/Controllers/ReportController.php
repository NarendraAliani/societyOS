<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csv;
use App\Helpers\Excel;
use App\Helpers\Pdf;
use App\Models\Asset;
use App\Models\Complaint;
use App\Models\Expense;
use App\Models\Flat;
use App\Models\Income;
use App\Models\MaintenanceBill;
use App\Models\ParkingSlot;
use App\Models\Society;
use App\Models\Staff;
use App\Models\Visitor;
use App\Services\PenaltyService;

final class ReportController
{
    public function index(): void
    {
        $pageTitle = 'Reports';
        require __DIR__ . '/../Views/reports/index.php';
    }

    private function dateRange(): array
    {
        return [
            $_GET['from'] ?? date('Y-m-01'),
            $_GET['to'] ?? date('Y-m-d'),
        ];
    }

    /**
     * Dispatches to CSV/PDF/Excel export based on ?format=, reusing the same header map
     * (label => row key) each report already builds for CSV — every format shows the same
     * columns. Each export helper exits the request; this returns normally when no format
     * (or an unrecognized one) is requested, so the caller falls through to the HTML view.
     */
    private function exportIfRequested(string $filenameBase, string $title, array $headers, array $rows): void
    {
        $format = $_GET['format'] ?? '';
        if ($format === 'csv') {
            Csv::export("{$filenameBase}.csv", $headers, $rows);
        } elseif ($format === 'pdf') {
            Pdf::export("{$filenameBase}.pdf", $title, $headers, $rows);
        } elseif ($format === 'xlsx') {
            Excel::export("{$filenameBase}.xlsx", $headers, $rows);
        }
    }

    public function collection(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = MaintenanceBill::collectionReport(Society::currentId(), $from, $to);

        $headers = [
            'Date' => 'paid_at', 'Bill No' => 'bill_number', 'Flat' => 'flat_number',
            'Wing' => 'wing_name', 'Amount' => 'amount', 'Mode' => 'payment_mode', 'Reference' => 'reference_number',
        ];
        $this->exportIfRequested('collection-report', 'Collection Report', $headers, $rows);

        $pageTitle = 'Collection Report';
        $total = array_sum(array_column($rows, 'amount'));
        require __DIR__ . '/../Views/reports/collection.php';
    }

    public function defaulters(): void
    {
        $rows = MaintenanceBill::defaulters(Society::currentId());
        foreach ($rows as &$bill) {
            $penalty = PenaltyService::recalculate((int) $bill['id']);
            $bill['penalty_amount'] = $penalty['penalty_amount'] ?? 0;
            $bill['penalty'] = $penalty;
        }
        unset($bill);

        $headers = [
            'Flat' => 'flat_number', 'Wing' => 'wing_name', 'Bill No' => 'bill_number',
            'Due Date' => 'due_date', 'Days Overdue' => 'days_overdue', 'Outstanding' => 'outstanding',
            'Penalty' => 'penalty_amount',
        ];
        $this->exportIfRequested('defaulter-report', 'Defaulter Report', $headers, $rows);

        $pageTitle = 'Defaulter Report';
        $defaulters = $rows;
        require __DIR__ . '/../Views/billing/defaulters.php';
    }

    public function income(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = Income::forDateRange(Society::currentId(), $from, $to);

        $headers = [
            'Date' => 'income_date', 'Category' => 'category', 'Account' => 'account_name',
            'Description' => 'description', 'Amount' => 'amount',
        ];
        $this->exportIfRequested('income-report', 'Income Report', $headers, $rows);

        $pageTitle = 'Income Report';
        $total = array_sum(array_column($rows, 'amount'));
        require __DIR__ . '/../Views/reports/income.php';
    }

    public function expense(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = Expense::forDateRange(Society::currentId(), $from, $to);

        $headers = [
            'Date' => 'expense_date', 'Category' => 'category', 'Vendor' => 'vendor_name',
            'Account' => 'account_name', 'Amount' => 'amount',
        ];
        $this->exportIfRequested('expense-report', 'Expense Report', $headers, $rows);

        $pageTitle = 'Expense Report';
        $total = array_sum(array_column($rows, 'amount'));
        require __DIR__ . '/../Views/reports/expense.php';
    }

    public function visitors(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = Visitor::forDateRange(Society::currentId(), $from, $to);

        $headers = [
            'Check In' => 'check_in_at', 'Name' => 'name', 'Flat' => 'flat_number',
            'Wing' => 'wing_name', 'Purpose' => 'purpose', 'Status' => 'approval_status',
        ];
        $this->exportIfRequested('visitor-report', 'Visitor Report', $headers, $rows);

        $pageTitle = 'Visitor Report';
        require __DIR__ . '/../Views/reports/visitors.php';
    }

    public function complaints(): void
    {
        $rows = Complaint::summaryByCategory(Society::currentId());

        $headers = [
            'Category' => 'category_name', 'Total' => 'total', 'Open' => 'open_count',
            'In Progress' => 'in_progress_count', 'Resolved' => 'resolved_count', 'Closed' => 'closed_count',
        ];
        $this->exportIfRequested('complaint-report', 'Complaint Report', $headers, $rows);

        $pageTitle = 'Complaint Report';
        require __DIR__ . '/../Views/reports/complaints.php';
    }

    public function staff(): void
    {
        $rows = Staff::allForSociety(Society::currentId());

        $headers = [
            'Name' => 'name', 'Designation' => 'designation', 'Phone' => 'phone',
            'Joining Date' => 'joining_date', 'Status' => 'status',
        ];
        $this->exportIfRequested('staff-report', 'Staff Report', $headers, $rows);

        $pageTitle = 'Staff Report';
        require __DIR__ . '/../Views/reports/staff.php';
    }

    public function assets(): void
    {
        $rows = Asset::allForSociety(Society::currentId());

        $headers = [
            'Name' => 'name', 'Category' => 'category_name', 'Location' => 'location',
            'Purchase Date' => 'purchase_date', 'Purchase Cost' => 'purchase_cost',
            'Warranty Expiry' => 'warranty_expiry', 'Status' => 'status',
        ];
        $this->exportIfRequested('asset-report', 'Asset Report', $headers, $rows);

        $pageTitle = 'Asset Report';
        require __DIR__ . '/../Views/reports/assets.php';
    }

    public function occupancy(): void
    {
        $rows = Flat::allForSociety(Society::currentId());

        $headers = [
            'Wing' => 'wing_name', 'Flat' => 'flat_number', 'Type' => 'flat_type', 'Occupancy' => 'occupancy_status',
        ];
        $this->exportIfRequested('occupancy-report', 'Occupancy Report', $headers, $rows);

        $pageTitle = 'Occupancy Report';
        $vacant = count(array_filter($rows, fn ($f) => $f['occupancy_status'] === 'vacant'));
        require __DIR__ . '/../Views/reports/occupancy.php';
    }

    public function parking(): void
    {
        $rows = ParkingSlot::allForSociety(Society::currentId());

        $headers = [
            'Slot' => 'slot_number', 'Type' => 'slot_type', 'Allocated' => 'is_allocated',
            'Flat' => 'flat_number', 'Vehicle' => 'registration_number',
        ];
        $this->exportIfRequested('parking-report', 'Parking Report', $headers, $rows);

        $pageTitle = 'Parking Report';
        require __DIR__ . '/../Views/reports/parking.php';
    }
}
