<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

use App\Helpers\Router;
use App\Helpers\Session;
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\LandingController;
use App\Controllers\SocietyController;
use App\Controllers\MemberController;
use App\Controllers\BillingController;
use App\Controllers\VehicleController;
use App\Controllers\AccountingController;
use App\Controllers\VisitorController;
use App\Controllers\ComplaintController;
use App\Controllers\NoticeController;
use App\Controllers\StaffController;
use App\Controllers\AssetController;
use App\Controllers\ReportController;
use App\Controllers\AdminController;
use App\Controllers\BackupController;
use App\Controllers\ProfileController;

Session::start();

$router = new Router();

$auth = fn () => AuthMiddleware::handle();
$can = fn (string $permission) => PermissionMiddleware::require($permission);

$router->get('/', [LandingController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index'], [$auth]);

// Society Setup
$router->get('/society', [SocietyController::class, 'profile'], [$auth, $can('society.manage')]);
$router->post('/society', [SocietyController::class, 'updateProfile'], [$auth, $can('society.manage')]);

$router->get('/society/wings', [SocietyController::class, 'wings'], [$auth, $can('flats.manage')]);
$router->post('/society/wings', [SocietyController::class, 'storeWing'], [$auth, $can('flats.manage')]);
$router->post('/society/wings/{id}/delete', [SocietyController::class, 'deleteWing'], [$auth, $can('flats.manage')]);
$router->post('/society/wings/{id}', [SocietyController::class, 'updateWing'], [$auth, $can('flats.manage')]);
$router->get('/society/wings/{id}', [SocietyController::class, 'wingDetail'], [$auth, $can('flats.manage')]);

$router->post('/society/floors', [SocietyController::class, 'storeFloor'], [$auth, $can('flats.manage')]);
$router->post('/society/floors/{id}/delete', [SocietyController::class, 'deleteFloor'], [$auth, $can('flats.manage')]);
$router->post('/society/floors/{id}', [SocietyController::class, 'updateFloor'], [$auth, $can('flats.manage')]);
$router->get('/society/floors/{id}', [SocietyController::class, 'floorDetail'], [$auth, $can('flats.manage')]);

$router->post('/society/flats', [SocietyController::class, 'storeFlat'], [$auth, $can('flats.manage')]);
$router->post('/society/flats/{id}/delete', [SocietyController::class, 'deleteFlat'], [$auth, $can('flats.manage')]);
$router->post('/society/flats/{id}', [SocietyController::class, 'updateFlat'], [$auth, $can('flats.manage')]);

$router->get('/society/maintenance-heads', [SocietyController::class, 'maintenanceHeads'], [$auth, $can('billing.manage')]);
$router->post('/society/maintenance-heads', [SocietyController::class, 'storeMaintenanceHead'], [$auth, $can('billing.manage')]);
$router->post('/society/maintenance-heads/{id}/toggle', [SocietyController::class, 'toggleMaintenanceHead'], [$auth, $can('billing.manage')]);
$router->post('/society/maintenance-heads/{id}/delete', [SocietyController::class, 'deleteMaintenanceHead'], [$auth, $can('billing.manage')]);
$router->post('/society/maintenance-heads/{id}', [SocietyController::class, 'updateMaintenanceHead'], [$auth, $can('billing.manage')]);
$router->get('/society/maintenance-heads/{id}', [SocietyController::class, 'maintenanceHeadDetail'], [$auth, $can('billing.manage')]);
$router->post('/society/maintenance-heads/{id}/rates', [SocietyController::class, 'storeMaintenanceHeadRate'], [$auth, $can('billing.manage')]);
$router->post('/maintenance-head-rates/{id}/delete', [SocietyController::class, 'deleteMaintenanceHeadRate'], [$auth, $can('billing.manage')]);

// Residents
$router->get('/members', [MemberController::class, 'index'], [$auth, $can('members.view')]);
$router->get('/members/tenants', [MemberController::class, 'tenants'], [$auth, $can('members.view')]);
$router->get('/members/create', [MemberController::class, 'create'], [$auth, $can('members.manage')]);
$router->post('/members', [MemberController::class, 'store'], [$auth, $can('members.manage')]);
$router->get('/members/{id}', [MemberController::class, 'show'], [$auth, $can('members.view')]);
$router->post('/members/{id}', [MemberController::class, 'update'], [$auth, $can('members.manage')]);
$router->post('/members/{id}/delete', [MemberController::class, 'destroy'], [$auth, $can('members.manage')]);

$router->post('/members/{id}/family-members', [MemberController::class, 'storeFamilyMember'], [$auth, $can('members.manage')]);
$router->post('/family-members/{id}/delete', [MemberController::class, 'deleteFamilyMember'], [$auth, $can('members.manage')]);

$router->post('/members/{id}/emergency-contacts', [MemberController::class, 'storeEmergencyContact'], [$auth, $can('members.manage')]);
$router->post('/emergency-contacts/{id}/delete', [MemberController::class, 'deleteEmergencyContact'], [$auth, $can('members.manage')]);

$router->post('/members/{id}/documents', [MemberController::class, 'storeDocument'], [$auth, $can('members.manage')]);
$router->post('/documents/{id}/delete', [MemberController::class, 'deleteDocument'], [$auth, $can('members.manage')]);
$router->get('/documents/{id}/file', [MemberController::class, 'serveDocument'], [$auth, $can('members.view')]);

$router->post('/members/{id}/lease', [MemberController::class, 'storeLease'], [$auth, $can('members.manage')]);
$router->post('/leases/{id}', [MemberController::class, 'updateLease'], [$auth, $can('members.manage')]);
$router->get('/leases/{id}/agreement', [MemberController::class, 'serveLeaseDocument'], [$auth, $can('members.view')]);

// Maintenance Billing — static paths must be registered before the /billing/{id} wildcard
$router->get('/billing', [BillingController::class, 'index'], [$auth, $can('billing.view')]);
$router->get('/billing/generate', [BillingController::class, 'showGenerateForm'], [$auth, $can('billing.manage')]);
$router->post('/billing/generate', [BillingController::class, 'generate'], [$auth, $can('billing.manage')]);
$router->get('/billing/defaulters', [BillingController::class, 'defaulters'], [$auth, $can('billing.view')]);
$router->get('/billing/payments/{paymentId}/receipt', [BillingController::class, 'downloadReceipt'], [$auth, $can('billing.view')]);
$router->get('/billing/{id}', [BillingController::class, 'show'], [$auth, $can('billing.view')]);
$router->post('/billing/{id}/payments', [BillingController::class, 'recordPayment'], [$auth, $can('billing.manage')]);

// Vehicles & Parking
$router->get('/vehicles', [VehicleController::class, 'index'], [$auth, $can('vehicles.manage')]);
$router->get('/vehicles/create', [VehicleController::class, 'create'], [$auth, $can('vehicles.manage')]);
$router->post('/vehicles', [VehicleController::class, 'store'], [$auth, $can('vehicles.manage')]);
$router->post('/vehicles/{id}/delete', [VehicleController::class, 'destroy'], [$auth, $can('vehicles.manage')]);

// Static /vehicles/parking* routes must be registered before the generic POST /vehicles/{id} below,
// otherwise POST /vehicles/parking would wrongly match /vehicles/{id} with id="parking".
$router->get('/vehicles/parking', [VehicleController::class, 'parkingIndex'], [$auth, $can('vehicles.manage')]);
$router->post('/vehicles/parking', [VehicleController::class, 'storeSlot'], [$auth, $can('vehicles.manage')]);

// /vehicles/parking/rates must be registered before the /vehicles/parking/{id} wildcard below,
// same reasoning as the /vehicles/{id} note above.
$router->get('/vehicles/parking/rates', [VehicleController::class, 'parkingRates'], [$auth, $can('vehicles.manage')]);
$router->post('/vehicles/parking/rates', [VehicleController::class, 'storeParkingRate'], [$auth, $can('vehicles.manage')]);
$router->post('/parking-rates/{id}/delete', [VehicleController::class, 'deleteParkingRate'], [$auth, $can('vehicles.manage')]);

$router->get('/vehicles/parking/{id}', [VehicleController::class, 'slotDetail'], [$auth, $can('vehicles.manage')]);
$router->post('/vehicles/parking/{id}', [VehicleController::class, 'updateSlot'], [$auth, $can('vehicles.manage')]);
$router->post('/vehicles/parking/{id}/delete', [VehicleController::class, 'deleteSlot'], [$auth, $can('vehicles.manage')]);
$router->post('/vehicles/parking/{id}/allocate', [VehicleController::class, 'allocate'], [$auth, $can('vehicles.manage')]);

$router->post('/vehicles/{id}', [VehicleController::class, 'update'], [$auth, $can('vehicles.manage')]);
$router->post('/parking-allocations/{id}/release', [VehicleController::class, 'release'], [$auth, $can('vehicles.manage')]);

// Accounting
$router->get('/accounting/accounts', [AccountingController::class, 'accounts'], [$auth, $can('accounting.view')]);
$router->post('/accounting/accounts', [AccountingController::class, 'storeAccount'], [$auth, $can('accounting.manage')]);
$router->post('/accounting/accounts/{id}', [AccountingController::class, 'updateAccount'], [$auth, $can('accounting.manage')]);
$router->get('/accounting/income', [AccountingController::class, 'income'], [$auth, $can('accounting.view')]);
$router->post('/accounting/income', [AccountingController::class, 'storeIncome'], [$auth, $can('accounting.manage')]);
$router->get('/accounting/expenses', [AccountingController::class, 'expenses'], [$auth, $can('accounting.view')]);
$router->post('/accounting/expenses', [AccountingController::class, 'storeExpense'], [$auth, $can('accounting.manage')]);
$router->get('/accounting/vendors', [AccountingController::class, 'vendors'], [$auth, $can('accounting.view')]);
$router->post('/accounting/vendors', [AccountingController::class, 'storeVendor'], [$auth, $can('accounting.manage')]);
$router->post('/accounting/vendors/{id}/delete', [AccountingController::class, 'deleteVendor'], [$auth, $can('accounting.manage')]);
$router->post('/accounting/vendors/{id}', [AccountingController::class, 'updateVendor'], [$auth, $can('accounting.manage')]);
$router->get('/accounting/ledger', [AccountingController::class, 'ledger'], [$auth, $can('accounting.view')]);

// Visitors & Security
$router->get('/visitors', [VisitorController::class, 'index'], [$auth, $can('visitors.manage')]);
$router->post('/visitors', [VisitorController::class, 'store'], [$auth, $can('visitors.manage')]);
$router->post('/visitors/{id}/approve', [VisitorController::class, 'approve'], [$auth, $can('visitors.manage')]);
$router->post('/visitors/{id}/reject', [VisitorController::class, 'reject'], [$auth, $can('visitors.manage')]);
$router->post('/visitors/{id}/checkout', [VisitorController::class, 'checkout'], [$auth, $can('visitors.manage')]);

$router->get('/visitors/passes', [VisitorController::class, 'passes'], [$auth, $can('visitors.manage')]);
$router->post('/visitors/passes', [VisitorController::class, 'storePass'], [$auth, $can('visitors.manage')]);
$router->post('/visitors/passes/verify', [VisitorController::class, 'verifyPass'], [$auth, $can('visitors.manage')]);

$router->get('/visitors/deliveries', [VisitorController::class, 'deliveries'], [$auth, $can('visitors.manage')]);
$router->post('/visitors/deliveries', [VisitorController::class, 'storeDelivery'], [$auth, $can('visitors.manage')]);
$router->post('/visitors/deliveries/{id}/collect', [VisitorController::class, 'collectDelivery'], [$auth, $can('visitors.manage')]);

// Complaints — static paths before the /complaints/{id} wildcard
$router->get('/complaints', [ComplaintController::class, 'index'], [$auth, $can('complaints.view')]);
$router->get('/complaints/create', [ComplaintController::class, 'create'], [$auth, $can('complaints.manage')]);
$router->post('/complaints', [ComplaintController::class, 'store'], [$auth, $can('complaints.manage')]);
$router->get('/complaints/categories', [ComplaintController::class, 'categories'], [$auth, $can('complaints.manage')]);
$router->post('/complaints/categories', [ComplaintController::class, 'storeCategory'], [$auth, $can('complaints.manage')]);
$router->post('/complaints/categories/{id}/delete', [ComplaintController::class, 'deleteCategory'], [$auth, $can('complaints.manage')]);
$router->post('/complaints/categories/{id}', [ComplaintController::class, 'updateCategory'], [$auth, $can('complaints.manage')]);
$router->get('/complaints/{id}', [ComplaintController::class, 'show'], [$auth, $can('complaints.view')]);
$router->post('/complaints/{id}/updates', [ComplaintController::class, 'addUpdate'], [$auth, $can('complaints.manage')]);

// Notices, Events, Polls — static paths before the /notices/{id} wildcard would go
$router->get('/notices', [NoticeController::class, 'index'], [$auth, $can('notices.manage')]);
$router->post('/notices', [NoticeController::class, 'store'], [$auth, $can('notices.manage')]);
$router->post('/notices/{id}/delete', [NoticeController::class, 'destroy'], [$auth, $can('notices.manage')]);

$router->get('/notices/events', [NoticeController::class, 'events'], [$auth, $can('notices.manage')]);
$router->post('/notices/events', [NoticeController::class, 'storeEvent'], [$auth, $can('notices.manage')]);
$router->post('/notices/events/{id}/delete', [NoticeController::class, 'deleteEvent'], [$auth, $can('notices.manage')]);

$router->get('/notices/polls', [NoticeController::class, 'polls'], [$auth, $can('notices.manage')]);
$router->post('/notices/polls', [NoticeController::class, 'storePoll'], [$auth, $can('notices.manage')]);
$router->get('/notices/polls/{id}', [NoticeController::class, 'showPoll'], [$auth, $can('notices.manage')]);
$router->post('/notices/polls/{id}/vote', [NoticeController::class, 'vote'], [$auth, $can('notices.manage')]);

// Staff — static paths before the /staff/{id} wildcard
$router->get('/staff', [StaffController::class, 'index'], [$auth, $can('staff.manage')]);
$router->get('/staff/create', [StaffController::class, 'create'], [$auth, $can('staff.manage')]);
$router->post('/staff', [StaffController::class, 'store'], [$auth, $can('staff.manage')]);
$router->get('/staff/attendance', [StaffController::class, 'attendance'], [$auth, $can('staff.manage')]);
$router->post('/staff/attendance', [StaffController::class, 'markAttendance'], [$auth, $can('staff.manage')]);
$router->get('/staff/leave', [StaffController::class, 'leave'], [$auth, $can('staff.manage')]);
$router->post('/staff/leave', [StaffController::class, 'storeLeave'], [$auth, $can('staff.manage')]);
$router->post('/staff/leave/{id}', [StaffController::class, 'updateLeaveStatus'], [$auth, $can('staff.manage')]);
$router->post('/staff/payroll/{id}/mark-paid', [StaffController::class, 'markPayrollPaid'], [$auth, $can('staff.manage')]);
$router->get('/staff/{id}', [StaffController::class, 'show'], [$auth, $can('staff.manage')]);
$router->post('/staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'], [$auth, $can('staff.manage')]);
$router->post('/staff/{id}/delete', [StaffController::class, 'destroy'], [$auth, $can('staff.manage')]);
$router->post('/staff/{id}/payroll', [StaffController::class, 'storePayroll'], [$auth, $can('staff.manage')]);
$router->post('/staff/{id}/police-verification', [StaffController::class, 'updatePoliceVerification'], [$auth, $can('staff.manage')]);
$router->get('/staff/{id}/file/{type}', [StaffController::class, 'serveFile'], [$auth, $can('staff.manage')]);
// Must come after /staff/leave and /staff/attendance above, or POST to those would wrongly match this wildcard.
$router->post('/staff/{id}', [StaffController::class, 'update'], [$auth, $can('staff.manage')]);

// Assets — static paths before the /assets/{id} wildcard
$router->get('/assets', [AssetController::class, 'index'], [$auth, $can('assets.manage')]);
$router->get('/assets/create', [AssetController::class, 'create'], [$auth, $can('assets.manage')]);
$router->post('/assets', [AssetController::class, 'store'], [$auth, $can('assets.manage')]);
$router->get('/assets/categories', [AssetController::class, 'categories'], [$auth, $can('assets.manage')]);
$router->post('/assets/categories', [AssetController::class, 'storeCategory'], [$auth, $can('assets.manage')]);
$router->post('/assets/categories/{id}', [AssetController::class, 'updateCategory'], [$auth, $can('assets.manage')]);
$router->post('/assets/categories/{id}/delete', [AssetController::class, 'deleteCategory'], [$auth, $can('assets.manage')]);
$router->get('/assets/{id}', [AssetController::class, 'show'], [$auth, $can('assets.manage')]);
$router->post('/assets/{id}/status', [AssetController::class, 'setStatus'], [$auth, $can('assets.manage')]);
$router->post('/assets/{id}/amc', [AssetController::class, 'storeAmc'], [$auth, $can('assets.manage')]);
$router->post('/assets/{id}/service', [AssetController::class, 'storeService'], [$auth, $can('assets.manage')]);
// Must come after /assets/categories above, or POST to that would wrongly match this wildcard.
$router->post('/assets/{id}', [AssetController::class, 'update'], [$auth, $can('assets.manage')]);

// Reports
$router->get('/reports', [ReportController::class, 'index'], [$auth, $can('reports.view')]);
$router->get('/reports/collection', [ReportController::class, 'collection'], [$auth, $can('reports.view')]);
$router->get('/reports/defaulters', [ReportController::class, 'defaulters'], [$auth, $can('reports.view')]);
$router->get('/reports/income', [ReportController::class, 'income'], [$auth, $can('reports.view')]);
$router->get('/reports/expense', [ReportController::class, 'expense'], [$auth, $can('reports.view')]);
$router->get('/reports/visitors', [ReportController::class, 'visitors'], [$auth, $can('reports.view')]);
$router->get('/reports/complaints', [ReportController::class, 'complaints'], [$auth, $can('reports.view')]);
$router->get('/reports/staff', [ReportController::class, 'staff'], [$auth, $can('reports.view')]);
$router->get('/reports/assets', [ReportController::class, 'assets'], [$auth, $can('reports.view')]);
$router->get('/reports/occupancy', [ReportController::class, 'occupancy'], [$auth, $can('reports.view')]);
$router->get('/reports/parking', [ReportController::class, 'parking'], [$auth, $can('reports.view')]);

// Administration
$router->get('/admin/users', [AdminController::class, 'users'], [$auth, $can('users.manage')]);
$router->get('/admin/users/create', [AdminController::class, 'createUser'], [$auth, $can('users.manage')]);
$router->post('/admin/users', [AdminController::class, 'storeUser'], [$auth, $can('users.manage')]);
$router->post('/admin/users/{id}', [AdminController::class, 'updateUser'], [$auth, $can('users.manage')]);
$router->post('/admin/users/{id}/reset-password', [AdminController::class, 'resetPassword'], [$auth, $can('users.manage')]);

$router->get('/admin/roles', [AdminController::class, 'roles'], [$auth, $can('users.manage')]);
$router->get('/admin/roles/{id}', [AdminController::class, 'editRole'], [$auth, $can('users.manage')]);
$router->post('/admin/roles/{id}/permissions', [AdminController::class, 'updateRolePermissions'], [$auth, $can('users.manage')]);

$router->get('/admin/activity-logs', [AdminController::class, 'activityLogs'], [$auth, $can('users.manage')]);

// Backup & Restore — hard-restricted to super_admin inside BackupController itself
// (Auth::role() check), not merely gated by a grantable permission key, given the blast
// radius of restore. $auth here only confirms the request is authenticated at all.
$router->get('/admin/backup', [BackupController::class, 'index'], [$auth]);
$router->post('/admin/backup', [BackupController::class, 'create'], [$auth]);
$router->get('/admin/backup/{filename}/download', [BackupController::class, 'download'], [$auth]);
$router->post('/admin/backup/{filename}/delete', [BackupController::class, 'delete'], [$auth]);
$router->post('/admin/backup/{filename}/restore', [BackupController::class, 'restoreFromList'], [$auth]);
$router->post('/admin/backup/restore-upload', [BackupController::class, 'restoreFromUpload'], [$auth]);

// Profile — any authenticated user, no specific permission required
$router->get('/profile', [ProfileController::class, 'show'], [$auth]);
$router->get('/profile/edit', [ProfileController::class, 'edit'], [$auth]);
$router->post('/profile', [ProfileController::class, 'update'], [$auth]);
$router->get('/profile/password', [ProfileController::class, 'showChangePassword'], [$auth]);
$router->post('/profile/password', [ProfileController::class, 'updatePassword'], [$auth]);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
