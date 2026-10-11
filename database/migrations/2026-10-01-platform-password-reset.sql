-- SocietyOS email password-reset support for platform administrators
-- The society users password_resets table already exists in database/schema.sql.
-- Run after taking a production backup.

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
