-- SocietyOS multi-role user accounts
-- BACKUP REQUIRED before production execution.
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
