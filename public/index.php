<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/database.php';

use App\Helpers\Router;
use App\Helpers\Session;
use App\Helpers\ErrorHandler;
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
use App\Controllers\SettingsController;
use App\Controllers\ResidentController;
use App\Controllers\PlatformController;
use App\Middleware\BackOfficeMiddleware;
use App\Middleware\PlatformAdminMiddleware;

Session::start();
ErrorHandler::register();

// Baseline browser security headers for every application response.
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net data:; img-src 'self' data: blob:; connect-src 'self'");
if (($_SERVER['HTTPS'] ?? '') === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

$router = new Router();

$auth = fn () => AuthMiddleware::handle();
$can = fn (string $permission) => PermissionMiddleware::require($permission);
$backOffice = fn () => BackOfficeMiddleware::handle();

$router->get('/', [LandingController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password', [AuthController::class, 'requestPasswordReset']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'completePasswordReset']);
$router->get('/platform/login', [PlatformController::class, 'showLogin']);
$router->get('/platform/forgot-password', [PlatformController::class, 'showForgotPassword']);
$router->post('/platform/forgot-password', [PlatformController::class, 'requestPasswordReset']);
$router->get('/platform/reset-password', [PlatformController::class, 'showResetPassword']);
$router->post('/platform/reset-password', [PlatformController::class, 'completePasswordReset']);
$router->post('/platform/login', [PlatformController::class, 'login']);
$router->get('/platform/societies', [PlatformController::class, 'societies'], [fn () => PlatformAdminMiddleware::handle()]);
$router->get('/platform/societies/create', [PlatformController::class, 'createSociety'], [fn () => PlatformAdminMiddleware::handle()]);
$router->post('/platform/societies', [PlatformController::class, 'storeSociety'], [fn () => PlatformAdminMiddleware::handle()]);
$router->get('/platform/password', [PlatformController::class, 'password'], [fn () => PlatformAdminMiddleware::handle()]);
$router->post('/platform/password', [PlatformController::class, 'updatePassword'], [fn () => PlatformAdminMiddleware::handle()]);
$router->get('/platform/logout', [PlatformController::class, 'logout'], [fn () => PlatformAdminMiddleware::handle()]);

$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index'], [$auth]);

$router->get('/resident', [ResidentController::class, 'home'], [$auth, $can('dashboard.view')]);
$router->get('/resident/bills', [ResidentController::class, 'bills'], [$auth, $can('billing.view')]);
$router->get('/resident/family', [ResidentController::class, 'family'], [$auth, $can('dashboard.view')]);
$router->get('/resident/vehicles', [ResidentController::class, 'vehicles'], [$auth, $can('dashboard.view')]);
$router->post('/resident/vehicles', [ResidentController::class, 'storeVehicle'], [$auth, $can('dashboard.view')]);
$router->post('/resident/vehicles/{id}', [ResidentController::class, 'updateVehicle'], [$auth, $can('dashboard.view')]);
$router->post('/resident/vehicles/{id}/delete', [ResidentController::class, 'deleteVehicle'], [$auth, $can('dashboard.view')]);
$router->get('/resident/documents', [ResidentController::class, 'documents'], [$auth, $can('dashboard.view')]);
$router->post('/resident/documents', [ResidentController::class, 'storeDocument'], [$auth, $can('dashboard.view')]);
$router->post('/resident/documents/{id}/delete', [ResidentController::class, 'deleteDocument'], [$auth, $can('dashboard.view')]);
$router->post('/resident/family-members', [ResidentController::class, 'storeFamilyMember'], [$auth, $can('dashboard.view')]);
$router->post('/resident/family-members/{id}/delete', [ResidentController::class, 'deleteFamilyMember'], [$auth, $can('dashboard.view')]);
$router->post('/resident/emergency-contacts', [ResidentController::class, 'storeEmergencyContact'], [$auth, $can('dashboard.view')]);
$router->post('/resident/emergency-contacts/{id}/delete', [ResidentController::class, 'deleteEmergencyContact'], [$auth, $can('dashboard.view')]);
$router->get('/resident/notices', [ResidentController::class, 'notices'], [$auth, $can('dashboard.view')]);
$router->get('/resident/complaints', [ResidentController::class, 'complaints'], [$auth, $can('complaints.view')]);
$router->post('/resident/complaints', [ResidentController::class, 'storeComplaint'], [$auth, $can('complaints.view')]);
$router->get('/resident/visitor-passes', [ResidentController::class, 'visitorPasses'], [$auth, $can('visitors.manage')]);
$router->post('/resident/visitor-passes', [ResidentController::class, 'storeVisitorPass'], [$auth, $can('visitors.manage')]);



// Society Setup
$router->get('/society', [SocietyController::class, 'profile'], [$auth, $backOffice, $can('society.manage')]);
$router->post('/society', [SocietyController::class, 'updateProfile'], [$auth, $backOffice, $can('society.manage')]);

$router->get('/society/wings', [SocietyController::class, 'wings'], [$auth, $backOffice, $can('flats.manage')]);
$router->post('/society/wings', [SocietyController::class, 'storeWing'], [$auth, $backOffice, $can('flats.manage')]);
$router->post('/society/wings/{id}/delete', [SocietyController::class, 'deleteWing'], [$auth, $backOffice, $can('flats.manage')]);
$router->post('/society/wings/{id}/configure', [SocietyController::class, 'configureWingStructure'], [$auth, $backOffice, $can('flats.manage')]);
$router->post('/society/wings/{id}', [SocietyController::class, 'updateWing'], [$auth, $backOffice, $can('flats.manage')]);
$router->get('/society/wings/{id}', [SocietyController::class, 'wingDetail'], [$auth, $backOffice, $can('flats.manage')]);

$router->post('/society/floors', [SocietyController::class, 'storeFloor'], [$auth, $backOffice, $can('flats.manage')]);
$router->post('/society/floors/{id}/delete', [SocietyController::class, 'deleteFloor'], [$auth, $backOffice, $can('flats.manage')]);
$router->post('/society/floors/{id}', [SocietyController::class, 'updateFloor'], [$auth, $backOffice, $can('flats.manage')]);
$router->get('/society/floors/{id}', [SocietyController::class, 'floorDetail'], [$auth, $backOffice, $can('flats.manage')]);

$router->post('/society/flats', [SocietyController::class, 'storeFlat'], [$auth, $backOffice, $can('flats.manage')]);
$router->post('/society/flats/{id}/delete', [SocietyController::class, 'deleteFlat'], [$auth, $backOffice, $can('flats.manage')]);
$router->post('/society/flats/{id}', [SocietyController::class, 'updateFlat'], [$auth, $backOffice, $can('flats.manage')]);

$router->get('/society/maintenance-heads', [SocietyController::class, 'maintenanceHeads'], [$auth, $backOffice, $can('billing.manage')]);
$router->post('/society/maintenance-heads', [SocietyController::class, 'storeMaintenanceHead'], [$auth, $backOffice, $can('billing.manage')]);
$router->post('/society/maintenance-heads/{id}/toggle', [SocietyController::class, 'toggleMaintenanceHead'], [$auth, $backOffice, $can('billing.manage')]);
$router->post('/society/maintenance-heads/{id}/delete', [SocietyController::class, 'deleteMaintenanceHead'], [$auth, $backOffice, $can('billing.manage')]);
$router->post('/society/maintenance-heads/{id}', [SocietyController::class, 'updateMaintenanceHead'], [$auth, $backOffice, $can('billing.manage')]);
$router->get('/society/maintenance-heads/{id}', [SocietyController::class, 'maintenanceHeadDetail'], [$auth, $backOffice, $can('billing.manage')]);
$router->post('/society/maintenance-heads/{id}/rates', [SocietyController::class, 'storeMaintenanceHeadRate'], [$auth, $backOffice, $can('billing.manage')]);
$router->post('/maintenance-head-rates/{id}/delete', [SocietyController::class, 'deleteMaintenanceHeadRate'], [$auth, $backOffice, $can('billing.manage')]);

// Residents
$router->get('/members', [MemberController::class, 'index'], [$auth, $backOffice, $can('members.view')]);
$router->get('/members/tenants', [MemberController::class, 'tenants'], [$auth, $backOffice, $can('members.view')]);
$router->get('/members/create', [MemberController::class, 'create'], [$auth, $backOffice, $can('members.manage')]);
$router->post('/members', [MemberController::class, 'store'], [$auth, $backOffice, $can('members.manage')]);
$router->get('/members/{id}', [MemberController::class, 'show'], [$auth, $backOffice, $can('members.view')]);
$router->post('/members/{id}', [MemberController::class, 'update'], [$auth, $backOffice, $can('members.manage')]);
$router->post('/members/{id}/delete', [MemberController::class, 'destroy'], [$auth, $backOffice, $can('members.manage')]);

$router->post('/members/{id}/family-members', [MemberController::class, 'storeFamilyMember'], [$auth, $backOffice, $can('members.manage')]);
$router->post('/family-members/{id}/delete', [MemberController::class, 'deleteFamilyMember'], [$auth, $backOffice, $can('members.manage')]);

$router->post('/members/{id}/emergency-contacts', [MemberController::class, 'storeEmergencyContact'], [$auth, $backOffice, $can('members.manage')]);
$router->post('/emergency-contacts/{id}/delete', [MemberController::class, 'deleteEmergencyContact'], [$auth, $backOffice, $can('members.manage')]);

$router->post('/members/{id}/documents', [MemberController::class, 'storeDocument'], [$auth, $backOffice, $can('members.manage')]);
$router->post('/documents/{id}/delete', [MemberController::class, 'deleteDocument'], [$auth, $backOffice, $can('members.manage')]);
$router->get('/documents/{id}/file', [MemberController::class, 'serveDocument'], [$auth]);

$router->post('/members/{id}/lease', [MemberController::class, 'storeLease'], [$auth, $backOffice, $can('members.manage')]);
$router->post('/leases/{id}', [MemberController::class, 'updateLease'], [$auth, $backOffice, $can('members.manage')]);
$router->get('/leases/{id}/agreement', [MemberController::class, 'serveLeaseDocument'], [$auth, $backOffice, $can('members.view')]);

// Maintenance Billing — static paths must be registered before the /billing/{id} wildcard
$router->get('/billing', [BillingController::class, 'index'], [$auth, $backOffice, $can('billing.view')]);
$router->get('/billing/generate', [BillingController::class, 'showGenerateForm'], [$auth, $backOffice, $can('billing.manage')]);
$router->post('/billing/generate', [BillingController::class, 'generate'], [$auth, $backOffice, $can('billing.manage')]);
$router->get('/billing/defaulters', [BillingController::class, 'defaulters'], [$auth, $backOffice, $can('billing.view')]);
$router->get('/billing/payments/{paymentId}/receipt', [BillingController::class, 'downloadReceipt'], [$auth, $backOffice, $can('billing.view')]);
$router->get('/billing/{id}', [BillingController::class, 'show'], [$auth, $backOffice, $can('billing.view')]);
$router->post('/billing/{id}/payments', [BillingController::class, 'recordPayment'], [$auth, $backOffice, $can('billing.manage')]);

// Vehicles & Parking
$router->get('/vehicles', [VehicleController::class, 'index'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->get('/vehicles/create', [VehicleController::class, 'create'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/vehicles', [VehicleController::class, 'store'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/vehicles/{id}/delete', [VehicleController::class, 'destroy'], [$auth, $backOffice, $can('vehicles.manage')]);

// Static /vehicles/parking* routes must be registered before the generic POST /vehicles/{id} below,
// otherwise POST /vehicles/parking would wrongly match /vehicles/{id} with id="parking".
$router->get('/vehicles/parking', [VehicleController::class, 'parkingIndex'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/vehicles/parking', [VehicleController::class, 'storeSlot'], [$auth, $backOffice, $can('vehicles.manage')]);

// /vehicles/parking/rates must be registered before the /vehicles/parking/{id} wildcard below,
// same reasoning as the /vehicles/{id} note above.
$router->get('/vehicles/parking/rates', [VehicleController::class, 'parkingRates'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/vehicles/parking/rates', [VehicleController::class, 'storeParkingRate'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/parking-rates/{id}/delete', [VehicleController::class, 'deleteParkingRate'], [$auth, $backOffice, $can('vehicles.manage')]);

$router->get('/vehicles/parking/{id}', [VehicleController::class, 'slotDetail'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/vehicles/parking/{id}', [VehicleController::class, 'updateSlot'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/vehicles/parking/{id}/delete', [VehicleController::class, 'deleteSlot'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/vehicles/parking/{id}/allocate', [VehicleController::class, 'allocate'], [$auth, $backOffice, $can('vehicles.manage')]);

$router->post('/vehicles/{id}', [VehicleController::class, 'update'], [$auth, $backOffice, $can('vehicles.manage')]);
$router->post('/parking-allocations/{id}/release', [VehicleController::class, 'release'], [$auth, $backOffice, $can('vehicles.manage')]);

// Accounting
$router->get('/accounting/accounts', [AccountingController::class, 'accounts'], [$auth, $backOffice, $can('accounting.view')]);
$router->post('/accounting/accounts', [AccountingController::class, 'storeAccount'], [$auth, $backOffice, $can('accounting.manage')]);
$router->post('/accounting/accounts/{id}', [AccountingController::class, 'updateAccount'], [$auth, $backOffice, $can('accounting.manage')]);
$router->get('/accounting/income', [AccountingController::class, 'income'], [$auth, $backOffice, $can('accounting.view')]);
$router->post('/accounting/income', [AccountingController::class, 'storeIncome'], [$auth, $backOffice, $can('accounting.manage')]);
$router->get('/accounting/expenses', [AccountingController::class, 'expenses'], [$auth, $backOffice, $can('accounting.view')]);
$router->post('/accounting/expenses', [AccountingController::class, 'storeExpense'], [$auth, $backOffice, $can('accounting.manage')]);
$router->get('/accounting/vendors', [AccountingController::class, 'vendors'], [$auth, $backOffice, $can('accounting.view')]);
$router->post('/accounting/vendors', [AccountingController::class, 'storeVendor'], [$auth, $backOffice, $can('accounting.manage')]);
$router->post('/accounting/vendors/{id}/delete', [AccountingController::class, 'deleteVendor'], [$auth, $backOffice, $can('accounting.manage')]);
$router->post('/accounting/vendors/{id}', [AccountingController::class, 'updateVendor'], [$auth, $backOffice, $can('accounting.manage')]);
$router->get('/accounting/ledger', [AccountingController::class, 'ledger'], [$auth, $backOffice, $can('accounting.view')]);
$router->get('/accounting/cash-book', [AccountingController::class, 'cashBook'], [$auth, $backOffice, $can('accounting.view')]);
$router->get('/accounting/bank-book', [AccountingController::class, 'bankBook'], [$auth, $backOffice, $can('accounting.view')]);

$router->get('/accounting/reports', [AccountingController::class, 'reports'], [$auth, $backOffice, $can('accounting.view')]);
$router->get('/accounting/reports/trial-balance', [AccountingController::class, 'trialBalance'], [$auth, $backOffice, $can('accounting.view')]);
$router->get('/accounting/reports/income-expense-statement', [AccountingController::class, 'incomeExpenseStatement'], [$auth, $backOffice, $can('accounting.view')]);
$router->get('/accounting/reports/balance-sheet', [AccountingController::class, 'balanceSheet'], [$auth, $backOffice, $can('accounting.view')]);

// Visitors & Security
$router->get('/visitors', [VisitorController::class, 'index'], [$auth, $backOffice, $can('visitors.manage')]);
$router->post('/visitors', [VisitorController::class, 'store'], [$auth, $backOffice, $can('visitors.manage')]);
$router->post('/visitors/{id}/approve', [VisitorController::class, 'approve'], [$auth, $backOffice, $can('visitors.manage')]);
$router->post('/visitors/{id}/reject', [VisitorController::class, 'reject'], [$auth, $backOffice, $can('visitors.manage')]);
$router->post('/visitors/{id}/checkout', [VisitorController::class, 'checkout'], [$auth, $backOffice, $can('visitors.manage')]);

$router->get('/visitors/passes', [VisitorController::class, 'passes'], [$auth, $backOffice, $can('visitors.manage')]);
$router->post('/visitors/passes', [VisitorController::class, 'storePass'], [$auth, $backOffice, $can('visitors.manage')]);
$router->post('/visitors/passes/verify', [VisitorController::class, 'verifyPass'], [$auth, $backOffice, $can('visitors.manage')]);

$router->get('/visitors/deliveries', [VisitorController::class, 'deliveries'], [$auth, $backOffice, $can('visitors.manage')]);
$router->post('/visitors/deliveries', [VisitorController::class, 'storeDelivery'], [$auth, $backOffice, $can('visitors.manage')]);
$router->post('/visitors/deliveries/{id}/collect', [VisitorController::class, 'collectDelivery'], [$auth, $backOffice, $can('visitors.manage')]);

// Complaints — static paths before the /complaints/{id} wildcard
$router->get('/complaints', [ComplaintController::class, 'index'], [$auth, $backOffice, $can('complaints.view')]);
$router->get('/complaints/create', [ComplaintController::class, 'create'], [$auth, $backOffice, $can('complaints.manage')]);
$router->post('/complaints', [ComplaintController::class, 'store'], [$auth, $backOffice, $can('complaints.manage')]);
$router->get('/complaints/categories', [ComplaintController::class, 'categories'], [$auth, $backOffice, $can('complaints.manage')]);
$router->post('/complaints/categories', [ComplaintController::class, 'storeCategory'], [$auth, $backOffice, $can('complaints.manage')]);
$router->post('/complaints/categories/{id}/delete', [ComplaintController::class, 'deleteCategory'], [$auth, $backOffice, $can('complaints.manage')]);
$router->post('/complaints/categories/{id}', [ComplaintController::class, 'updateCategory'], [$auth, $backOffice, $can('complaints.manage')]);
$router->get('/complaints/{id}', [ComplaintController::class, 'show'], [$auth, $backOffice, $can('complaints.view')]);
$router->post('/complaints/{id}/updates', [ComplaintController::class, 'addUpdate'], [$auth, $backOffice, $can('complaints.manage')]);

// Notices, Events, Polls — static paths before the /notices/{id} wildcard would go
$router->get('/notices', [NoticeController::class, 'index'], [$auth, $backOffice, $can('notices.manage')]);
$router->post('/notices', [NoticeController::class, 'store'], [$auth, $backOffice, $can('notices.manage')]);
$router->post('/notices/{id}/delete', [NoticeController::class, 'destroy'], [$auth, $backOffice, $can('notices.manage')]);

$router->get('/notices/events', [NoticeController::class, 'events'], [$auth, $backOffice, $can('notices.manage')]);
$router->post('/notices/events', [NoticeController::class, 'storeEvent'], [$auth, $backOffice, $can('notices.manage')]);
$router->post('/notices/events/{id}/delete', [NoticeController::class, 'deleteEvent'], [$auth, $backOffice, $can('notices.manage')]);

$router->get('/notices/polls', [NoticeController::class, 'polls'], [$auth, $backOffice, $can('notices.manage')]);
$router->post('/notices/polls', [NoticeController::class, 'storePoll'], [$auth, $backOffice, $can('notices.manage')]);
$router->get('/notices/polls/{id}', [NoticeController::class, 'showPoll'], [$auth, $backOffice, $can('notices.manage')]);
$router->post('/notices/polls/{id}/vote', [NoticeController::class, 'vote'], [$auth, $backOffice, $can('notices.manage')]);

// Staff — static paths before the /staff/{id} wildcard
$router->get('/staff', [StaffController::class, 'index'], [$auth, $backOffice, $can('staff.manage')]);
$router->get('/staff/create', [StaffController::class, 'create'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff', [StaffController::class, 'store'], [$auth, $backOffice, $can('staff.manage')]);
$router->get('/staff/attendance', [StaffController::class, 'attendance'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff/attendance', [StaffController::class, 'markAttendance'], [$auth, $backOffice, $can('staff.manage')]);
$router->get('/staff/leave', [StaffController::class, 'leave'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff/leave', [StaffController::class, 'storeLeave'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff/leave/{id}', [StaffController::class, 'updateLeaveStatus'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff/payroll/{id}/mark-paid', [StaffController::class, 'markPayrollPaid'], [$auth, $backOffice, $can('staff.manage')]);
$router->get('/staff/{id}', [StaffController::class, 'show'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff/{id}/delete', [StaffController::class, 'destroy'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff/{id}/payroll', [StaffController::class, 'storePayroll'], [$auth, $backOffice, $can('staff.manage')]);
$router->post('/staff/{id}/police-verification', [StaffController::class, 'updatePoliceVerification'], [$auth, $backOffice, $can('staff.manage')]);
$router->get('/staff/{id}/file/{type}', [StaffController::class, 'serveFile'], [$auth, $backOffice, $can('staff.manage')]);
// Must come after /staff/leave and /staff/attendance above, or POST to those would wrongly match this wildcard.
$router->post('/staff/{id}', [StaffController::class, 'update'], [$auth, $backOffice, $can('staff.manage')]);

// Assets — static paths before the /assets/{id} wildcard
$router->get('/assets', [AssetController::class, 'index'], [$auth, $backOffice, $can('assets.manage')]);
$router->get('/assets/create', [AssetController::class, 'create'], [$auth, $backOffice, $can('assets.manage')]);
$router->post('/assets', [AssetController::class, 'store'], [$auth, $backOffice, $can('assets.manage')]);
$router->get('/assets/categories', [AssetController::class, 'categories'], [$auth, $backOffice, $can('assets.manage')]);
$router->post('/assets/categories', [AssetController::class, 'storeCategory'], [$auth, $backOffice, $can('assets.manage')]);
$router->post('/assets/categories/{id}', [AssetController::class, 'updateCategory'], [$auth, $backOffice, $can('assets.manage')]);
$router->post('/assets/categories/{id}/delete', [AssetController::class, 'deleteCategory'], [$auth, $backOffice, $can('assets.manage')]);
$router->get('/assets/{id}', [AssetController::class, 'show'], [$auth, $backOffice, $can('assets.manage')]);
$router->post('/assets/{id}/status', [AssetController::class, 'setStatus'], [$auth, $backOffice, $can('assets.manage')]);
$router->post('/assets/{id}/amc', [AssetController::class, 'storeAmc'], [$auth, $backOffice, $can('assets.manage')]);
$router->post('/assets/{id}/service', [AssetController::class, 'storeService'], [$auth, $backOffice, $can('assets.manage')]);
// Must come after /assets/categories above, or POST to that would wrongly match this wildcard.
$router->post('/assets/{id}', [AssetController::class, 'update'], [$auth, $backOffice, $can('assets.manage')]);

// Reports
$router->get('/reports', [ReportController::class, 'index'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/collection', [ReportController::class, 'collection'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/defaulters', [ReportController::class, 'defaulters'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/income', [ReportController::class, 'income'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/expense', [ReportController::class, 'expense'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/visitors', [ReportController::class, 'visitors'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/complaints', [ReportController::class, 'complaints'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/staff', [ReportController::class, 'staff'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/assets', [ReportController::class, 'assets'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/occupancy', [ReportController::class, 'occupancy'], [$auth, $backOffice, $can('reports.view')]);
$router->get('/reports/parking', [ReportController::class, 'parking'], [$auth, $backOffice, $can('reports.view')]);

// Administration
$router->get('/admin/users', [AdminController::class, 'users'], [$auth, $backOffice, $can('users.manage')]);
$router->post('/admin/users/{id}/roles', [AdminController::class, 'addUserRole'], [$auth, $backOffice, $can('users.manage')]);
$router->post('/admin/users/{id}/roles/{roleId}/default', [AdminController::class, 'setDefaultRole'], [$auth, $backOffice, $can('users.manage')]);
$router->post('/admin/users/{id}/roles/{roleId}/remove', [AdminController::class, 'removeUserRole'], [$auth, $backOffice, $can('users.manage')]);
$router->post('/admin/users/{id}/status', [AdminController::class, 'updateUserStatus'], [$auth, $backOffice, $can('users.manage')]);

$router->get('/admin/users/create', [AdminController::class, 'createUser'], [$auth, $backOffice, $can('users.manage')]);
$router->get('/admin/users/resident-candidates', [AdminController::class, 'availableResidentCandidates'], [$auth, $backOffice, $can('users.manage')]);
$router->post('/admin/users', [AdminController::class, 'storeUser'], [$auth, $backOffice, $can('users.manage')]);
$router->post('/admin/users/{id}/reset-password', [AdminController::class, 'resetPassword'], [$auth, $backOffice, $can('users.manage')]);

$router->get('/admin/roles', [AdminController::class, 'roles'], [$auth, $backOffice, $can('users.manage')]);
$router->get('/admin/roles/{id}', [AdminController::class, 'editRole'], [$auth, $backOffice, $can('users.manage')]);
$router->post('/admin/roles/{id}/permissions', [AdminController::class, 'updateRolePermissions'], [$auth, $backOffice, $can('users.manage')]);

$router->get('/admin/activity-logs', [AdminController::class, 'activityLogs'], [$auth, $backOffice, $can('users.manage')]);

// Backup & Restore — hard-restricted to super_admin inside BackupController itself
// (Auth::role() check), not merely gated by a grantable permission key, given the blast
// radius of restore. $auth here only confirms the request is authenticated at all.
$router->get('/admin/backup', [BackupController::class, 'index'], [$auth]);
$router->post('/admin/backup', [BackupController::class, 'create'], [$auth]);
$router->get('/admin/backup/{filename}/download', [BackupController::class, 'download'], [$auth]);
$router->post('/admin/backup/{filename}/delete', [BackupController::class, 'delete'], [$auth]);
$router->post('/admin/backup/{filename}/restore', [BackupController::class, 'restoreFromList'], [$auth]);
$router->post('/admin/backup/restore-upload', [BackupController::class, 'restoreFromUpload'], [$auth]);

$router->get('/admin/settings', [SettingsController::class, 'index'], [$auth, $backOffice, $can('settings.manage')]);
$router->post('/admin/settings', [SettingsController::class, 'update'], [$auth, $backOffice, $can('settings.manage')]);

// Profile — any authenticated user, no specific permission required
$router->get('/profile', [ProfileController::class, 'show'], [$auth]);
$router->get('/profile/edit', [ProfileController::class, 'edit'], [$auth]);
$router->post('/profile', [ProfileController::class, 'update'], [$auth]);
$router->get('/profile/password', [ProfileController::class, 'showChangePassword'], [$auth]);
$router->post('/profile/password', [ProfileController::class, 'updatePassword'], [$auth]);
$router->post('/switch-role', [AdminController::class, 'switchRole'], [$auth]);


$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
