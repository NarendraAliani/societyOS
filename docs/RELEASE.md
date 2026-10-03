# SocietyOS — Release & Deployment Procedure

## Standard release flow

SocietyOS uses a controlled release path:

1. Develop on a feature branch.
2. Validate the feature locally and against the documented business rules.
3. Merge the completed feature into `main`.
4. GitHub Actions runs `.github/workflows/production-build.yml`.
5. CI validates Composer configuration, installs production dependencies, runs PHP syntax checks, and runs regression contracts.
6. CI prepares the production `deploy` branch with `vendor/`.
7. CI deploys the production build to BigRock through strict FTPS.
8. If a database migration is required, take a production backup first and execute the versioned migration through BigRock phpMyAdmin.
9. Perform the feature's live smoke test.
10. Record any production-specific verification or known limitation in `docs/DECISIONS.md`.

## BigRock manual steps

### Code deployment
**None.** Code deployment is automatic after a successful push to `main`.

### Database migration
Manual action is required when a feature changes the production schema:

1. Open BigRock cPanel → phpMyAdmin.
2. Select `dairyikh_societyos`.
3. Take/confirm a current database backup before destructive or structural changes.
4. Open the versioned SQL migration from `database/migrations/`.
5. Execute it in phpMyAdmin.
6. Verify the expected table/index/column change.
7. Run the feature's live smoke test.

Do not make undocumented schema changes directly in production. The migration file in GitHub is the source of truth.

### Production configuration
`.env` remains server-side and is excluded from deployment. Configuration changes must be explicitly documented as manual BigRock steps.

## Regression prevention

Every recurring bug class should become an automated CI check where practical.

The linked-home bug demonstrated why UI-only testing is insufficient. The role selector originally had an implicit resident/tenant allow-list, so Accountant and other roles never requested their candidate list even though the backend had been changed to support all roles.

The regression contract at `tests/regression/linked_home_role_scope.php` checks that:

- the create-user UI always uses the role-scoped candidate endpoint;
- the UI contains no resident/tenant-only AJAX gate;
- the controller contains no resident/tenant-only candidate restriction;
- model eligibility remains role-agnostic;
- candidate selection continues to exclude flats already assigned to the selected role.

For future bugs, add a regression test to the same release workflow before considering the bug closed.


## Multi-role user migration

Before production deployment:

1. Take a fresh BigRock phpMyAdmin backup.
2. Run `database/migrations/2026-10-01-multi-role-users-preflight.sql` first. **Both result sets must be empty.** If either returns rows, stop and resolve the data issue before continuing.
3. Execute `database/migrations/2026-10-01-multi-role-users.sql`.
4. Verify one `user_roles` row exists for each existing user and verify both unique constraints are present.
5. Test login, role switching, existing-email role addition, same-flat/different-role allowance, and same-flat/same-role rejection.
6. Do not remove legacy `users.role_id/member_id` columns until production verification is complete.


## Multi-society context migration

The first multi-society slice adds a unique society code and makes the authenticated session the source of the active society context. Existing single-society login remains backward-compatible when the Society Code field is blank.

Production steps: take a backup, run database/migrations/2026-10-01-multi-society-context.sql, verify the existing society receives SOC-001, then smoke-test login and the dashboard. The society provisioning/admin directory is the next multi-society phase; do not create additional society records manually yet.


## Platform administration & society provisioning

The next multi-society phase adds a platform-level administrator console at /platform/login. It is intentionally separate from society-scoped users.

Before using the platform console in production:

1. Take a fresh BigRock phpMyAdmin backup.
2. Execute database/migrations/2026-10-01-platform-admin-provisioning.sql once.
3. The migration creates platform administrator tables and bootstraps the first platform administrator from the existing society super_admin credentials. The platform account is forced to change its password on first platform login.
4. Sign in at /platform/login and change the platform administrator password.
5. Use Platform → Create Society to provision additional societies. Do not manually insert additional society rows.

Provisioning creates the society boundary, current financial year, default settings, cash account, complaint/asset categories, maintenance heads/rates, and the society's first Super Admin atomically. The new Super Admin then signs in through the normal /login flow using the provisioned Society Code.


## 2026-10 quality hardening release

- Added tenant-scoped lookup hardening for society-owned records.
- Hardened session cookies and timeout handling.
- Added production-safe exception logging and response handling.
- Added CSP and browser security headers.
- Added global destructive-action confirmations.
- Added responsive table wrapping and long-table search.
- Added server-side SVG QR generation for visitor passes.
- Added targeted performance indexes to the fresh schema.
- Added migration database/migrations/2026-10-02-performance-indexes.sql for existing installations.

Operational note: the performance-index migration is not executed by the deployment workflow. Take a production backup and review/execute it separately when approved.
