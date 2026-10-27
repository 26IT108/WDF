-- Practical 14: audit trail for admin and data-management actions.
-- Import this file into the existing studenthub database using phpMyAdmin.
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    details VARCHAR(500) NOT NULL DEFAULT '',
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_user (user_id),
    KEY idx_audit_created (created_at),
    KEY idx_audit_action (action),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
