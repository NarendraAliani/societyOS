-- SocietyOS multi-role user accounts — PRODUCTION PREFLIGHT
-- Run this in BigRock phpMyAdmin BEFORE 2026-10-01-multi-role-users.sql.
-- Both result sets must be EMPTY.

-- 1) Existing users must have an active member with a valid flat.
--    Exception: the existing system super_admin account may be unlinked.
SELECT
    u.id,
    u.email,
    u.role_id,
    r.name AS role_name,
    u.member_id,
    m.status AS member_status,
    m.flat_id
FROM users u
JOIN roles r ON r.id=u.role_id
LEFT JOIN members m ON m.id = u.member_id
WHERE NOT (
    (r.name='super_admin' AND u.member_id IS NULL)
    OR (m.id IS NOT NULL AND m.status='active' AND m.flat_id IS NOT NULL)
);

-- 2) No existing flat may already have more than one user for the same role.
SELECT
    m.flat_id,
    u.role_id,
    COUNT(*) AS user_count
FROM users u
JOIN members m ON m.id = u.member_id
WHERE u.role_id IS NOT NULL
GROUP BY m.flat_id, u.role_id
HAVING COUNT(*) > 1;
