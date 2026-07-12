# SocietyOS

A single-society residential management system — maintenance billing, accounting, visitor management, complaints, staff, assets, and admin tooling for one housing society/apartment complex, built as a lightweight custom PHP application (no framework).

## Tech Stack

| Layer | Choice |
|---|---|
| Language | PHP 8.0+ |
| Database | MySQL / MariaDB (PDO, prepared statements throughout) |
| Web server | Apache (XAMPP), `mod_rewrite` |
| Frontend | Server-rendered PHP views, Bootstrap 5.3 (CDN), Font Awesome 6 (CDN) |
| Architecture | Custom lightweight MVC — no framework |
| PDF generation | [Dompdf](https://github.com/dompdf/dompdf) |
| Excel export | [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) |
| Mail | [PHPMailer](https://github.com/PHPMailer/PHPMailer) |
| Env config | [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) |

No JS framework, no build step, no bundler — Bootstrap/Font Awesome load from CDN and pages are plain server-rendered HTML with small inline `<script>` blocks for theme switching and confirmation dialogs.

## Architecture

A hand-rolled MVC, not a framework:

- **`public/index.php`** — single entry point. Registers every route on a custom `Router` (`app/Helpers/Router.php`), which matches `{param}` placeholders via regex. Static paths must be registered before wildcard routes sharing the same segment count (e.g. `/members/tenants` before `/members/{id}`) — order matters.
- **Controllers** (`app/Controllers/`) — one per module, thin: validate input, call a Model or Service, set flash messages, redirect or `require` a view.
- **Models** (`app/Models/`) — static methods wrapping PDO queries. No ORM.
- **Services** (`app/Services/`) — business logic that spans multiple models/tables (billing generation, penalty calculation, backup/restore, accounting postings).
- **Views** (`app/Views/`) — plain PHP templates. Each view `ob_start()`s its content, then `require`s the shared layout (`app/Views/layouts/app.php`) which wraps it in the sidebar/topbar chrome.
- **Middleware** (`app/Middleware/`) — `AuthMiddleware` (session check) and `PermissionMiddleware` (RBAC check), both applied per-route as closures.
- **Helpers** (`app/Helpers/`) — cross-cutting utilities: `Auth`, `Csrf`, `Session`, `Flash`, `Csv`/`Pdf`/`Excel` (report export), `FileUpload` (secure upload handling), `Router`.

### Request flow

```
public/index.php
  → Router::dispatch()
    → middleware (AuthMiddleware, PermissionMiddleware)
    → Controller action
      → Model (read/write DB) and/or Service (business logic)
      → Flash::set() + header('Location: ...')   [POST actions]
      → require View                             [GET actions]
```

### Key patterns used throughout

- **Effective-dated rates**: maintenance head charges and parking rates aren't a single mutable number — each is a child table (`maintenance_head_rates`, `parking_rates`) with `effective_from DATE`. Billing looks up the rate as of the *bill period's start date*, not "today." Past/current rates are immutable once effective; only a not-yet-effective scheduled rate can be deleted.
- **Recompute-on-view**: values like late-payment penalties don't run on a cron/scheduled job (none exists in this app) — they're recalculated fresh every time a bill or the defaulter report is opened, and the result is upserted into a small tracking table.
- **Authenticated-only file serving**: every upload (staff photos/ID proofs, resident documents, lease agreements, DB backups) lives under `uploads/`/`storage/` outside the web root and is served exclusively via a controller action that content-sniffs (`finfo_file()`) and auth-checks — never a direct static link.
- **Settings with config fallback**: admin-editable values (late-payment interest rate, upload size cap, theme/font-size defaults) live in a per-society `settings` key/value table; `Settings::get($societyId, $key, $default)` falls back to the `.env`-backed `config()` value when no override has been saved, so the feature is fully backward compatible with an empty table.
- **RBAC**: `roles` → `role_permissions` → `permissions`, checked via `Auth::can('module.action')` in views and `PermissionMiddleware::require(...)` on routes. `super_admin` implicitly has every permission.

## Features

- **Society Setup** — profile, Wings → Floors → Flats hierarchy, maintenance head configuration with effective-dated rate scheduling.
- **Residents** — owners and tenants, family members, emergency contacts, vehicles, document uploads (ID proofs, agreements), and a dedicated Tenants view with lease dates, owner linkage, and expiry-urgency badges.
- **Maintenance Billing** — bulk bill generation per flat per period (skips flats with nothing billable), payment recording with PDF receipts, a defaulter report, and persisted late-payment interest calculated fresh on every view against a configurable annual rate.
- **Vehicles & Parking** — vehicle registry, parking slot allocation (paid or free, overriding the slot-type default), effective-dated parking rate history.
- **Accounting** — cash/bank accounts, income/expense recording (auto-posts to a ledger), vendor registry, a raw ledger view, dedicated Cash Book / Bank Book views with running balances, and financial reports (Trial Balance, Income & Expense Statement, Balance Sheet — honestly scoped to what a single-entry cash ledger can actually support, not a full chart-of-accounts).
- **Visitors** — gate register with approve/reject/checkout, pre-authorized visitor passes, delivery log.
- **Complaints** — categorized complaints tied to a resident (and their flat, derived — never independently selectable), status timeline.
- **Notices, Events & Polls** — notice board, event calendar, polls with single-vote-per-member enforcement.
- **Staff** — employee records with photo/ID proof/DOB, a three-state police verification workflow, attendance, payroll, leave requests.
- **Assets** — asset register by category, AMC contract tracking, service history, vendor quick-add inline from the AMC form.
- **Reports** — collection, defaulters, income, expense, visitors, complaints, staff roster, asset register, occupancy, parking — every report exports as CSV, real PDF, and real Excel (`.xlsx`).
- **Dashboard** — KPI cards (flats, residents, dues, income/expense, account balance, parking occupancy, staff verification) and a merged "expiring soon" panel (AMC contracts, warranties, tenant leases) with red/amber urgency badges.
- **Admin** — user management with role/status control, role-permission editor, activity log (instrumented across all major write actions), a pure-PHP database Backup & Restore tool (no `mysqldump` dependency, auto safety-backup before every restore, hard-restricted to `super_admin`), and a Settings page for theme/font-size defaults, late-payment interest rate, and upload size cap.
- **Theming** — Light/Dark/Mid themes and Small/Medium/Large font sizes, each with a site-wide admin-configured default and a per-user override persisted in `localStorage`.

## Getting Started

### Requirements

- PHP 8.0+ with `pdo_mysql`, `fileinfo`, `zip` extensions
- MySQL/MariaDB
- Apache with `mod_rewrite` (XAMPP works out of the box)
- [Composer](https://getcomposer.org/)

### Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Configure environment
cp .env.example .env
# edit .env — set DB_DATABASE/DB_USERNAME/DB_PASSWORD and APP_URL to match your setup

# 3. Create the database and load schema + seed data
mysql -u root -p -e "CREATE DATABASE societyos"
mysql -u root -p societyos < database/schema.sql
mysql -u root -p societyos < database/seed.sql
```

Point your Apache vhost (or XAMPP's `htdocs/societyOS`) at the project root — the root `.htaccess` rewrites everything into `public/`, which is the actual document root for routing purposes.

### Default login

The seed data creates one `super_admin` account:

```
Email:    admin@demoresidency.local
Password: ChangeMe@123
```

**Change this password immediately after first login** — the seed comment says so for a reason.

### Project-specific `.env` keys

Beyond the standard `APP_*`/`DB_*`/`MAIL_*` keys, this app adds:

| Key | Purpose | Default |
|---|---|---|
| `SESSION_LIFETIME` | Session lifetime in minutes | `120` |
| `UPLOAD_MAX_SIZE_MB` | Max upload size — overridable per-society at runtime via Settings | `5` |
| `PENALTY_INTEREST_RATE_PERCENT` | Late-payment annual interest rate — overridable per-society at runtime via Settings | `18.0` |

## Project Structure

```
societyOS/
├── app/
│   ├── Controllers/     one per module (Member, Billing, Accounting, Staff, Asset, Admin, ...)
│   ├── Models/           static PDO query wrappers, one per table/entity
│   ├── Services/         cross-model business logic (Billing, Penalty, Accounting, Backup)
│   ├── Middleware/        AuthMiddleware, PermissionMiddleware
│   ├── Helpers/           Router, Auth, Csrf, Session, Flash, FileUpload, Csv/Pdf/Excel
│   └── Views/             per-module view folders + shared layouts/app.php
├── config/                app.php (env-backed config()), database.php (PDO singleton)
├── database/
│   ├── schema.sql          all 56 tables, CREATE TABLE IF NOT EXISTS
│   └── seed.sql             demo society, roles/permissions, one super_admin user
├── docs/
│   ├── DECISIONS.md         running decision log — every feature's reasoning, alternatives
│   │                        considered, and how it was verified against a live DB
│   └── SITEMAP.md           route-by-route build-status tracker
├── public/
│   ├── index.php            single entry point — router registration lives here
│   └── static/css/app.css   theme variables, KPI card styles, font-size overrides
├── storage/
│   ├── backups/              generated DB backups (gitignored)
│   ├── cache/, logs/         (gitignored)
├── uploads/                 staff/resident/asset files — outside web root, served only via
│                            authenticated streaming routes, never linked directly
└── vendor/                  Composer dependencies
```

## Security Notes

- All DB access via PDO prepared statements (`ATTR_EMULATE_PREPARES => false`) — no string-interpolated SQL.
- CSRF token on every state-changing form (`\App\Helpers\Csrf::field()` / `Csrf::verify()`).
- File uploads validated by actual content (`finfo_file()`), never client-supplied MIME type or filename; stored under a random filename outside the web root; served only through auth-checked streaming routes.
- Passwords hashed via PHP's `password_hash()`; login attempts rate-limited and logged to `login_history`.
- RBAC enforced per-route via middleware, not just hidden in the UI — a permission check runs before the controller action, so a raw request without the UI can't bypass it.
- Backup & Restore is hard-restricted to `super_admin` in code (not merely a grantable permission), and every restore takes an automatic safety backup of the current state first, since the operation is otherwise irreversible.

## Documentation

- **[`docs/DECISIONS.md`](docs/DECISIONS.md)** — the real design history of this project. Every non-trivial feature is documented with what was decided, why, what alternative was considered and rejected, and how it was verified end-to-end against a live database — not just code inspection.
- **[`docs/SITEMAP.md`](docs/SITEMAP.md)** — full route inventory with build status, kept in sync with every phase of development.

## License

Not currently licensed for public distribution.
