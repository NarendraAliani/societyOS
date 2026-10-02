-- SocietyOS production database migration — READ-ONLY PREFLIGHT
-- Run this FIRST in BigRock phpMyAdmin.
-- This script makes NO changes.
-- Expected: duplicate/invalid-result queries should return EMPTY result sets.

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

SELECT 'user_roles' AS object_name, IF(COUNT(*) > 0, 'PRESENT', 'MISSING') AS status
FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'user_roles'
UNION ALL
SELECT 'platform_admins', IF(COUNT(*) > 0, 'PRESENT', 'MISSING')
FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'platform_admins'
UNION ALL
SELECT 'platform_login_history', IF(COUNT(*) > 0, 'PRESENT', 'MISSING')
FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'platform_login_history'
UNION ALL
SELECT 'platform_password_resets', IF(COUNT(*) > 0, 'PRESENT', 'MISSING')
FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'platform_password_resets';

SELECT table_name, index_name, column_name, seq_in_index
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND index_name IN (
    'uq_society_code','uq_user_role','uq_flat_role',
    'idx_users_society_status','idx_members_society_status_flat',
    'idx_bills_society_due_status','idx_visitors_society_checkin',
    'idx_complaints_society_status_created','idx_activity_society_created',
    'idx_parking_alloc_slot_active','idx_documents_member_created',
    'idx_password_resets_token_used_expiry'
  )
ORDER BY table_name, index_name, seq_in_index;
