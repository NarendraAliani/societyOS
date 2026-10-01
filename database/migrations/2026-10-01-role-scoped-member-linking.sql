-- SocietyOS migration
-- Role-scoped member/user linking.
--
-- Rule:
--   A member can be linked to at most one user account for a given role.
--   The same member may be linked under a different role.
--
-- Example:
--   Narendra -> resident -> C-404
--   Narendra is excluded from the resident list thereafter.
--   Narendra is not excluded merely because another role is selected.
--
-- IMPORTANT:
-- Before applying this migration to an existing database, audit for duplicates:
--
-- SELECT society_id, role_id, member_id, COUNT(*) AS duplicate_count
-- FROM users
-- WHERE member_id IS NOT NULL
-- GROUP BY society_id, role_id, member_id
-- HAVING COUNT(*) > 1;
--
-- Resolve any returned duplicates before adding the unique index.

ALTER TABLE users
    ADD UNIQUE KEY uq_user_role_member (society_id, role_id, member_id);
