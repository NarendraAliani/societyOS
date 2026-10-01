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

Before production deployment, take a fresh BigRock phpMyAdmin backup and execute `database/migrations/2026-10-01-multi-role-users.sql`. Verify one `user_roles` row exists for each existing user, then test login, role switching, existing-email role addition, same-flat/different-role allowance, and same-flat/same-role rejection. Do not remove legacy `users.role_id/member_id` columns until production verification is complete.
