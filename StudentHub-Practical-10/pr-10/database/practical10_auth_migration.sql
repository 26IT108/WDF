-- Run this once if your studenthub database already exists from Practical 9.
-- If role already exists, skip the ALTER TABLE statement.
ALTER TABLE users ADD COLUMN role ENUM('admin', 'student') NOT NULL DEFAULT 'student';

CREATE TABLE IF NOT EXISTS remember_tokens (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    selector CHAR(24) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_remember_selector (selector),
    KEY idx_remember_user (user_id),
    CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: promote a trusted test account to admin after registration.
-- UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';
