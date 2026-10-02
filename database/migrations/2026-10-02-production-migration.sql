-- SocietyOS production database migration
-- APPROVED BY PROJECT OWNER: 2026-10-02
--
-- BEFORE RUNNING:
-- 1. Take a complete BigRock MySQL backup.
-- 2. Run 2026-10-02-production-preflight.sql.
-- 3. Confirm the preflight has no blocking rows.
-- 4. Run this file ONCE.
-- 5. Do not run the individual legacy migration files again after this file.
--
-- Consolidates:
-- multi-society context, multi-role users, platform administration,
-- platform password reset, and performance indexes.

SET SQL_SAFE_UPDATES = 0;
START TRANSACTION;

ALTER TABLE society
    ADD COLUMN code VARCHAR(30) NULL AFTER id;

UPDATE society
SET code = CONCAT('SOC-', LPAD(id, 3, '0'))
WHERE code IS NULL OR code = '';

ALTER TABLE society
    MODIFY COLUMN code VARCHAR(30) NOT NULL,
    ADD UNIQUE KEY uq_society_code (code);

CREATE TABLE IF NOT EXISTS user_roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    society_id INT UNSIGNED NOT NULL,
    role_id INT UNSIGNED NOT NULL,
    member_id INT UNSIGNED NULL,
    flat_id INT UNSIGNED NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (society_id) REFERENCES society(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (member_id) REFERENCES members(id),
    FOREIGN KEY (flat_id) REFERENCES flats(id),
    UNIQUE KEY uq_user_role (user_id, role_id),
    UNIQUE KEY uq_flat_role (flat_id, role_id),
    INDEX idx_user_roles_user (user_id),
    INDEX idx_user_roles_society_role (society_id, role_id)
) ENGINE=InnoDB;

INSERT INTO user_roles (user_id,society_id,role_id,member_id,flat_id,is_default)
SELECT u.id,u.society_id,u.role_id,u.member_id,m.flat_id,1
FROM users u
JOIN roles r ON r.id=u.role_id
LEFT JOIN members m ON m.id=u.member_id
WHERE (
    (m.id IS NOT NULL AND m.status='active' AND m.flat_id IS NOT NULL)
    OR (r.name='super_admin' AND u.member_id IS NULL)
)
AND NOT EXISTS (
    SELECT 1 FROM user_roles ur
    WHERE ur.user_id=u.id AND ur.role_id=u.role_id
);

CREATE TABLE IF NOT EXISTS platform_admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active','inactive','locked') NOT NULL DEFAULT 'active',
    must_change_password TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_platform_admin_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS platform_login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    platform_admin_id INT UNSIGNED NULL,
    email_attempted VARCHAR(150) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    status ENUM('success','failed') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (platform_admin_id) REFERENCES platform_admins(id) ON DELETE SET NULL,
    INDEX idx_platform_login_email_created (email_attempted, created_at)
) ENGINE=InnoDB;

INSERT INTO platform_admins (name,email,password_hash,status,must_change_password)
SELECT u.name,u.email,u.password_hash,'active',1
FROM users u JOIN roles r ON r.id=u.role_id
WHERE r.name='super_admin'
ORDER BY u.id ASC LIMIT 1
ON DUPLICATE KEY UPDATE name=VALUES(name);

CREATE TABLE IF NOT EXISTS platform_password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    platform_admin_id INT UNSIGNED NOT NULL,
    email_attempted VARCHAR(150) NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (platform_admin_id) REFERENCES platform_admins(id) ON DELETE CASCADE,
    INDEX idx_platform_reset_email_created (email_attempted, created_at),
    INDEX idx_platform_reset_token (token_hash),
    INDEX idx_platform_reset_expires (expires_at)
) ENGINE=InnoDB;

CREATE INDEX idx_users_society_status ON users (society_id, status);
CREATE INDEX idx_members_society_status_flat ON members (society_id, status, flat_id);
CREATE INDEX idx_bills_society_due_status ON maintenance_bills (society_id, due_date, status);
CREATE INDEX idx_visitors_society_checkin ON visitors (society_id, check_in_at);
CREATE INDEX idx_complaints_society_status_created ON complaints (society_id, status, created_at);
CREATE INDEX idx_activity_society_created ON activity_logs (society_id, created_at);
CREATE INDEX idx_parking_alloc_slot_active ON parking_allocations (parking_slot_id, allocated_to);
CREATE INDEX idx_documents_member_created ON documents (member_id, created_at);
CREATE INDEX idx_password_resets_token_used_expiry ON password_resets (token_hash, used_at, expires_at);

COMMIT;
SET SQL_SAFE_UPDATES = 1;

SELECT id, code FROM society ORDER BY id;
SELECT COUNT(*) AS user_role_rows FROM user_roles;
SELECT COUNT(*) AS platform_admin_rows FROM platform_admins;
SELECT COUNT(*) AS platform_login_history_rows FROM platform_login_history;
SELECT COUNT(*) AS platform_password_reset_rows FROM platform_password_resets;

SELECT table_name,index_name
FROM information_schema.statistics
WHERE table_schema=DATABASE()
AND index_name IN (
'uq_society_code','uq_user_role','uq_flat_role',
'idx_users_society_status','idx_members_society_status_flat',
'idx_bills_society_due_status','idx_visitors_society_checkin',
'idx_complaints_society_status_created','idx_activity_society_created',
'idx_parking_alloc_slot_active','idx_documents_member_created',
'idx_password_resets_token_used_expiry'
);
