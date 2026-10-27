CREATE DATABASE studenthub;

USE studenthub;

CREATE TABLE courses (
    course_id INT(11) NOT NULL AUTO_INCREMENT,
    course_name VARCHAR(100) NOT NULL,
    PRIMARY KEY (course_id)
);

INSERT INTO courses (course_name)
VALUES
('Information Technology'),
('Computer Science'),
('Computer Engineering');

CREATE TABLE students (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    course_id INT(11),
    year INT(11),
    gender VARCHAR(20),
    PRIMARY KEY (id),
    FOREIGN KEY (course_id)
        REFERENCES courses(course_id)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT
);


-- Practical 9: secure user registration table (MySQLi + password hashing)
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    username VARCHAR(30) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- Practical 10: persistent remember-me tokens
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

-- To promote a test account to administrator, run this manually in phpMyAdmin:
-- UPDATE users SET role = 'admin' WHERE email = 'your-admin-email@example.com';


-- Practical 12: event management (fresh database setup)
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
