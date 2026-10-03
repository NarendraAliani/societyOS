-- SocietyOS platform administration + society provisioning foundation
-- BACKUP REQUIRED before production execution.

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

INSERT INTO platform_admins (name, email, password_hash, status, must_change_password)
SELECT u.name, u.email, u.password_hash, 'active', 1
FROM users u JOIN roles r ON r.id = u.role_id
WHERE r.name = 'super_admin'
ORDER BY u.id ASC LIMIT 1
ON DUPLICATE KEY UPDATE name = VALUES(name);
