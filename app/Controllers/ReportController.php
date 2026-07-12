<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csv;
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

    public function collection(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = MaintenanceBill::collectionReport(Society::currentId(), $from, $to);

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('collection-report.csv', [
                'Date' => 'paid_at', 'Bill No' => 'bill_number', 'Flat' => 'flat_number',
                'Wing' => 'wing_name', 'Amount' => 'amount', 'Mode' => 'payment_mode', 'Reference' => 'reference_number',
            ], $rows);
        }

        $pageTitle = 'Collection Report';
        $total = array_sum(array_column($rows, 'amount'));
        require __DIR__ . '/../Views/reports/collection.php';
    }

    public function defaulters(): void
    {
        $rows = MaintenanceBill::defaulters(Society::currentId());

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('defaulter-report.csv', [
                'Flat' => 'flat_number', 'Wing' => 'wing_name', 'Bill No' => 'bill_number',
                'Due Date' => 'due_date', 'Days Overdue' => 'days_overdue', 'Outstanding' => 'outstanding',
            ], $rows);
        }

        $pageTitle = 'Defaulter Report';
        $defaulters = $rows;
        require __DIR__ . '/../Views/billing/defaulters.php';
    }

    public function income(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = Income::forDateRange(Society::currentId(), $from, $to);

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('income-report.csv', [
                'Date' => 'income_date', 'Category' => 'category', 'Account' => 'account_name',
                'Description' => 'description', 'Amount' => 'amount',
            ], $rows);
        }

        $pageTitle = 'Income Report';
        $total = array_sum(array_column($rows, 'amount'));
        require __DIR__ . '/../Views/reports/income.php';
    }

    public function expense(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = Expense::forDateRange(Society::currentId(), $from, $to);

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('expense-report.csv', [
                'Date' => 'expense_date', 'Category' => 'category', 'Vendor' => 'vendor_name',
                'Account' => 'account_name', 'Amount' => 'amount',
            ], $rows);
        }

        $pageTitle = 'Expense Report';
        $total = array_sum(array_column($rows, 'amount'));
        require __DIR__ . '/../Views/reports/expense.php';
    }

    public function visitors(): void
    {
        [$from, $to] = $this->dateRange();
        $rows = Visitor::forDateRange(Society::currentId(), $from, $to);

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('visitor-report.csv', [
                'Check In' => 'check_in_at', 'Name' => 'name', 'Flat' => 'flat_number',
                'Wing' => 'wing_name', 'Purpose' => 'purpose', 'Status' => 'approval_status',
            ], $rows);
        }

        $pageTitle = 'Visitor Report';
        require __DIR__ . '/../Views/reports/visitors.php';
    }

    public function complaints(): void
    {
        $rows = Complaint::summaryByCategory(Society::currentId());

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('complaint-report.csv', [
                'Category' => 'category_name', 'Total' => 'total', 'Open' => 'open_count',
                'In Progress' => 'in_progress_count', 'Resolved' => 'resolved_count', 'Closed' => 'closed_count',
            ], $rows);
        }

        $pageTitle = 'Complaint Report';
        require __DIR__ . '/../Views/reports/complaints.php';
    }

    public function staff(): void
    {
        $rows = Staff::allForSociety(Society::currentId());

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('staff-report.csv', [
                'Name' => 'name', 'Designation' => 'designation', 'Phone' => 'phone',
                'Joining Date' => 'joining_date', 'Status' => 'status',
            ], $rows);
        }

        $pageTitle = 'Staff Report';
        require __DIR__ . '/../Views/reports/staff.php';
    }

    public function assets(): void
    {
        $rows = Asset::allForSociety(Society::currentId());

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('asset-report.csv', [
                'Name' => 'name', 'Category' => 'category_name', 'Location' => 'location',
                'Purchase Date' => 'purchase_date', 'Purchase Cost' => 'purchase_cost',
                'Warranty Expiry' => 'warranty_expiry', 'Status' => 'status',
            ], $rows);
        }

        $pageTitle = 'Asset Report';
        require __DIR__ . '/../Views/reports/assets.php';
    }

    public function occupancy(): void
    {
        $rows = Flat::allForSociety(Society::currentId());

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('occupancy-report.csv', [
                'Wing' => 'wing_name', 'Flat' => 'flat_number', 'Type' => 'flat_type', 'Occupancy' => 'occupancy_status',
            ], $rows);
        }

        $pageTitle = 'Occupancy Report';
        $vacant = count(array_filter($rows, fn ($f) => $f['occupancy_status'] === 'vacant'));
        require __DIR__ . '/../Views/reports/occupancy.php';
    }

    public function parking(): void
    {
        $rows = ParkingSlot::allForSociety(Society::currentId());

        if (($_GET['format'] ?? '') === 'csv') {
            Csv::export('parking-report.csv', [
                'Slot' => 'slot_number', 'Type' => 'slot_type', 'Allocated' => 'is_allocated',
                'Flat' => 'flat_number', 'Vehicle' => 'registration_number',
            ], $rows);
        }

        $pageTitle = 'Parking Report';
        require __DIR__ . '/../Views/reports/parking.php';
    }
}
