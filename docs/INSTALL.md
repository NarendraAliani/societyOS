# SocietyOS — Installation Guide

Target: PHP 8.0+/8.x, MySQL 8.x or MariaDB 10.4+, Apache with mod_rewrite. Verified locally on XAMPP (PHP 8.0.28, MariaDB 10.4.28).

## 1. Get the code onto the server

Copy the project into your web root (e.g. `htdocs/societyOS` for XAMPP, or a subdomain document root on cPanel).

## 2. Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

`vendor/` is not committed — this step is required (PHPMailer, DomPDF, phpdotenv).

## 3. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE societyos CHARACTER SET utf8mb4;"
mysql -u root -p societyos < database/schema.sql
mysql -u root -p societyos < database/seed.sql
```

`seed.sql` creates one demo society, RBAC roles/permissions, a demo wing/floor/flat set, and **one super admin login**:

| Field | Value |
|---|---|
| Email | `admin@demoresidency.local` |
| Password | `ChangeMe@123` |

**Change this password immediately after first login** (`must_change_password` is set on the seeded row — wire this flag into the login flow before going live, see Known Limitations below). Edit `society`, `wings`, `floors`, `flats` rows afterward to match the real building, or truncate and re-insert with real data.

## 4. Configure environment

```bash
cp .env.example .env
```

Edit `.env`:
- `APP_URL` — full base URL including any subpath (e.g. `http://localhost/societyOS/public` on XAMPP, or `https://yourdomain.com` on cPanel with the document root pointed at `public/`).
- `DB_*` — database credentials.
- `MAIL_*` — SMTP creds for PHPMailer (bill notifications, password reset). Left blank by default — email sending will fail until filled in.
- `SMS_*` — left blank; wire to your chosen SMS gateway before enabling SMS notices.
- `APP_KEY` — generate a random 32+ byte value, e.g. `php -r "echo bin2hex(random_bytes(32));"`.

## 5. Point the web server at `public/`

**cPanel / production Apache**: set the domain/subdomain's document root to `public/`, not the project root. This keeps `app/`, `config/`, `database/`, `.env` outside the web-servable path.

**XAMPP (this local setup)**: the project root `.htaccess` at [`/.htaccess`](../.htaccess) rewrites all requests into `public/`, so `http://localhost/societyOS/` works without changing Apache's `DocumentRoot`. Requires `mod_rewrite` enabled (default in XAMPP) and `AllowOverride All` for the htdocs directory.

## 6. File permissions

Ensure the web server user can write to:
- `storage/logs/`
- `storage/cache/`
- `uploads/` (and subfolders: `documents/`, `staff/`, `assets/`)

## 7. First login

Visit `/login`, sign in with the seeded super admin credentials above, change the password, then proceed to Society Setup to replace demo data with the real society's wings/floors/flats.

## Known limitations (Phase 1 — foundation only)

This phase ships: folder structure, full DB schema (54 tables), RBAC (roles/permissions), session-based auth with CSRF + login rate-limiting, and the dashboard shell. **Not yet built**: the 20+ feature modules listed in [`SITEMAP.md`](SITEMAP.md) (residents, billing, accounting, visitors, complaints, notices, staff, assets, reports, admin CRUD screens) and the `must_change_password` enforcement on first login. See [`DECISIONS.md`](DECISIONS.md) for phasing rationale.
