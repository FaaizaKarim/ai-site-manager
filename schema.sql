-- AI Site Manager — Database Schema
-- Import this file via phpMyAdmin (http://localhost:8080/phpmyadmin)

CREATE DATABASE IF NOT EXISTS ai_site_manager
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ai_site_manager;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name          VARCHAR(100) NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE sites (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    name        VARCHAR(255) NOT NULL,
    url         VARCHAR(500) DEFAULT '',
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    site_id    INT NOT NULL,
    title      VARCHAR(255) NOT NULL,
    slug       VARCHAR(255) NOT NULL,
    content    LONGTEXT,
    status     ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ai_logs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    page_id      INT NOT NULL,
    action       VARCHAR(50) NOT NULL,
    user_message TEXT,
    ai_response  TEXT,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(255) NOT NULL,
    token      VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used       TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin user: admin@example.com / admin123
INSERT INTO users (email, password_hash, name) VALUES (
    'admin@example.com',
    '$2b$10$l49xdBe2K0kLifwtnxz.3eG5Pd/d8jhUg.rdNVDsIl8p3R9llXL4S',
    'Admin'
);
