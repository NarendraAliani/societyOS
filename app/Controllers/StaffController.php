<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\FileUpload;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Society;
use App\Models\Staff;

final class StaffController
{
    public function index(): void
    {
        $pageTitle = 'Staff';
        $staff = Staff::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/staff/index.php';
    }

    public function create(): void
    {
        $pageTitle = 'Add Staff';
        require __DIR__ . '/../Views/staff/create.php';
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Name is required.');
            header('Location: /staff/create');
            exit;
        }

        try {
            $photoPath = FileUpload::storeImage($_FILES['photo'] ?? [], 'staff');
            $idProofPath = FileUpload::storeDocument($_FILES['id_proof'] ?? [], 'staff');
        } catch (\RuntimeException $e) {
            Flash::set('error', $e->getMessage());
            header('Location: /staff/create');
            exit;
        }

        $id = Staff::create(Society::currentId(), [
            'name' => $name,
            'designation' => trim((string) ($_POST['designation'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'joining_date' => $_POST['joining_date'] ?? '',
            'photo_path' => $photoPath,
            'id_proof_path' => $idProofPath,
        ]);

        ActivityLog::log('staff', 'create', "Added staff member \"{$name}\"");
        Flash::set('success', "Staff member \"{$name}\" added.");
        header("Location: /staff/{$id}");
        exit;
    }

    public function show(string $id): void
    {
        $pageTitle = 'Staff Detail';
        $staff = Staff::find((int) $id);
        if (!$staff) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $payroll = Payroll::forStaff((int) $id);
        require __DIR__ . '/../Views/staff/show.php';
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Name is required.');
            header("Location: /staff/{$id}");
            exit;
        }

        try {
            $photoPath = FileUpload::storeImage($_FILES['photo'] ?? [], 'staff');
            $idProofPath = FileUpload::storeDocument($_FILES['id_proof'] ?? [], 'staff');
        } catch (\RuntimeException $e) {
            Flash::set('error', $e->getMessage());
            header("Location: /staff/{$id}");
            exit;
        }

        Staff::update((int) $id, [
            'name' => $name,
            'designation' => trim((string) ($_POST['designation'] ?? '')),
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'address' => trim((string) ($_POST['address'] ?? '')),
            'date_of_birth' => $_POST['date_of_birth'] ?? '',
            'joining_date' => $_POST['joining_date'] ?? '',
        ]);

        // Only overwrite the stored path if a new file actually came through this request —
        // leaving the field blank on an edit must never wipe out an existing photo/document.
        if ($photoPath !== null) {
            Staff::updatePhoto((int) $id, $photoPath);
        }
        if ($idProofPath !== null) {
            Staff::updateIdProof((int) $id, $idProofPath);
        }

        ActivityLog::log('staff', 'update', "Updated staff member \"{$name}\" (id {$id})");
        Flash::set('success', 'Staff details updated.');
        header("Location: /staff/{$id}");
        exit;
    }

    public function updatePoliceVerification(string $id): void
    {
        $this->verifyCsrf();

        $status = $_POST['police_verification_status'] ?? '';
        if (!in_array($status, ['pending', 'verified', 'not_verified'], true)) {
            Flash::set('error', 'Invalid verification status.');
            header("Location: /staff/{$id}");
            exit;
        }

        try {
            $docPath = FileUpload::storeDocument($_FILES['police_verification_doc'] ?? [], 'staff');
        } catch (\RuntimeException $e) {
            Flash::set('error', $e->getMessage());
            header("Location: /staff/{$id}");
            exit;
        }

        Staff::updatePoliceVerification((int) $id, $status, $_POST['police_verification_date'] ?? null ?: null, $docPath);
        ActivityLog::log('staff', 'police_verification', "Staff id {$id} verification set to \"{$status}\"");

        Flash::set('success', 'Police verification status updated.');
        header("Location: /staff/{$id}");
        exit;
    }

    /**
     * Streams an uploaded staff file (photo / ID proof / police verification certificate)
     * after the route's own auth+permission middleware has already run. uploads/ sits
     * outside the web root specifically so this is the only way to reach these files —
     * see FileUpload's doc comment.
     */
    public function serveFile(string $id, string $type): void
    {
        $staff = Staff::find((int) $id);
        $columnMap = [
            'photo' => 'photo_path',
            'id_proof' => 'id_proof_path',
            'police_doc' => 'police_verification_doc_path',
        ];

        if (!$staff || !isset($columnMap[$type]) || empty($staff[$columnMap[$type]])) {
            http_response_code(404);
            exit('Not found.');
        }

        $fullPath = dirname(__DIR__, 2) . '/uploads/' . $staff[$columnMap[$type]];
        if (!is_file($fullPath)) {
            http_response_code(404);
            exit('Not found.');
        }

        $contentTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));

        header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($fullPath));
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    public function toggleStatus(string $id): void
    {
        $this->verifyCsrf();
        $staff = Staff::find((int) $id);
        Staff::setStatus((int) $id, $staff && $staff['status'] === 'active' ? 'inactive' : 'active');
        Flash::set('success', 'Staff status updated.');
        header('Location: /staff');
        exit;
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $staff = Staff::find((int) $id);
        Staff::delete((int) $id);
        ActivityLog::log('staff', 'delete', 'Removed staff member "' . ($staff['name'] ?? $id) . '"');
        Flash::set('success', 'Staff member removed.');
        header('Location: /staff');
        exit;
    }

    public function attendance(): void
    {
        $pageTitle = 'Attendance';
        $date = $_GET['date'] ?? date('Y-m-d');
        $rows = Attendance::forDate(Society::currentId(), $date);
        require __DIR__ . '/../Views/staff/attendance.php';
    }

    public function markAttendance(): void
    {
        $this->verifyCsrf();

        $date = $_POST['attendance_date'] ?? date('Y-m-d');
        $statuses = $_POST['status'] ?? [];

        foreach ($statuses as $staffId => $status) {
            if (in_array($status, ['present', 'absent', 'half_day', 'leave'], true)) {
                Attendance::mark((int) $staffId, $date, $status, Auth::id());
            }
        }

        Flash::set('success', 'Attendance saved.');
        header("Location: /staff/attendance?date={$date}");
        exit;
    }

    public function storePayroll(string $staffId): void
    {
        $this->verifyCsrf();

        $period = trim((string) ($_POST['pay_period'] ?? ''));
        $basic = $_POST['basic_amount'] ?? '';
        $deductions = $_POST['deductions'] ?? '0';

        if ($period === '' || !is_numeric($basic)) {
            Flash::set('error', 'Pay period and a numeric basic amount are required.');
            header("Location: /staff/{$staffId}");
            exit;
        }

        Payroll::create((int) $staffId, $period, (float) $basic, is_numeric($deductions) ? (float) $deductions : 0.0);

        Flash::set('success', 'Payroll entry added.');
        header("Location: /staff/{$staffId}");
        exit;
    }

    public function markPayrollPaid(string $id): void
    {
        $this->verifyCsrf();
        $staffId = $_POST['staff_id'] ?? '';
        Payroll::markPaid((int) $id);
        Flash::set('success', 'Marked as paid.');
        header("Location: /staff/{$staffId}");
        exit;
    }

    public function leave(): void
    {
        $pageTitle = 'Leave Requests';
        $staff = Staff::allForSociety(Society::currentId());
        $leaveRequests = LeaveRequest::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/staff/leave.php';
    }

    public function storeLeave(): void
    {
        $this->verifyCsrf();

        $staffId = (int) ($_POST['staff_id'] ?? 0);
        $fromDate = $_POST['from_date'] ?? '';
        $toDate = $_POST['to_date'] ?? '';

        if ($staffId <= 0 || !$fromDate || !$toDate) {
            Flash::set('error', 'Staff and a valid date range are required.');
        } else {
            LeaveRequest::create($staffId, $fromDate, $toDate, trim((string) ($_POST['reason'] ?? '')) ?: null);
            Flash::set('success', 'Leave request logged.');
        }
        header('Location: /staff/leave');
        exit;
    }

    public function updateLeaveStatus(string $id): void
    {
        $this->verifyCsrf();
        $status = ($_POST['status'] ?? '') === 'approved' ? 'approved' : 'rejected';
        LeaveRequest::setStatus((int) $id, $status);
        Flash::set('success', 'Leave request updated.');
        header('Location: /staff/leave');
        exit;
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
