-- Practical 12: Event Management CRUD and validated poster uploads
-- Select the existing studenthub database before running this script.
USE studenthub;

CREATE TABLE IF NOT EXISTS events (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('Technical','Workshop','Sports','Cultural','Career','Other') NOT NULL DEFAULT 'Other',
    event_date DATETIME NOT NULL,
    status ENUM('upcoming','completed','cancelled') NOT NULL DEFAULT 'upcoming',
    poster_path VARCHAR(255) DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_events_date (event_date),
    KEY idx_events_status (status),
    KEY idx_events_category (category),
    CONSTRAINT fk_events_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
