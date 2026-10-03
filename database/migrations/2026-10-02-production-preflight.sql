-- SocietyOS production database migration — READ-ONLY PREFLIGHT
-- Run this FIRST in BigRock phpMyAdmin.
-- This script makes NO changes.
-- Expected: duplicate/invalid-result queries should return EMPTY result sets.
-- This file intentionally avoids information_schema for BigRock shared hosting.

SELECT DATABASE() AS current_database, NOW() AS checked_at;

SELECT
    u.id, u.email, r.name AS role_name, u.member_id,
    m.status AS member_status, m.flat_id
FROM users u
JOIN roles r ON r.id = u.role_id
LEFT JOIN members m ON m.id = u.member_id
WHERE NOT (
    (r.name = 'super_admin' AND u.member_id IS NULL)
    OR (m.id IS NOT NULL AND m.status = 'active' AND m.flat_id IS NOT NULL)
);

SELECT
    m.flat_id, u.role_id, COUNT(*) AS user_count
FROM users u
JOIN members m ON m.id = u.member_id
WHERE u.role_id IS NOT NULL
GROUP BY m.flat_id, u.role_id
HAVING COUNT(*) > 1;

SELECT id, code FROM society ORDER BY id;

-- Shared-hosting compatibility: do not query information_schema here.
-- Some BigRock/cPanel MySQL accounts deny access to information_schema
-- even when the account has full privileges on its own application database.
SHOW TABLES LIKE 'user_roles';
SHOW TABLES LIKE 'platform_admins';
SHOW TABLES LIKE 'platform_login_history';
SHOW TABLES LIKE 'platform_password_resets';

-- These SHOW INDEX checks are intentionally limited to tables that should
-- already exist before the consolidated migration is run.
SHOW INDEX FROM society;
SHOW INDEX FROM users;
SHOW INDEX FROM members;
SHOW INDEX FROM maintenance_bills;
SHOW INDEX FROM visitors;
SHOW INDEX FROM complaints;
SHOW INDEX FROM activity_logs;
SHOW INDEX FROM parking_allocations;
SHOW INDEX FROM documents;
SHOW INDEX FROM password_resets;
