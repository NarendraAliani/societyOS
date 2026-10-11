-- SocietyOS performance indexes for multi-society growth
-- BACKUP REQUIRED before production execution.
-- These indexes target recurring tenant/date/status filters. Review with EXPLAIN on
-- a production-sized dataset before execution if the database is very large.

CREATE INDEX idx_users_society_status ON users (society_id, status);
CREATE INDEX idx_members_society_status_flat ON members (society_id, status, flat_id);
CREATE INDEX idx_bills_society_due_status ON maintenance_bills (society_id, due_date, status);
CREATE INDEX idx_visitors_society_checkin ON visitors (society_id, check_in_at);
CREATE INDEX idx_complaints_society_status_created ON complaints (society_id, status, created_at);
CREATE INDEX idx_activity_society_created ON activity_logs (society_id, created_at);
CREATE INDEX idx_parking_alloc_slot_active ON parking_allocations (parking_slot_id, allocated_to);
CREATE INDEX idx_documents_member_created ON documents (member_id, created_at);
CREATE INDEX idx_password_resets_token_used_expiry ON password_resets (token_hash, used_at, expires_at);
