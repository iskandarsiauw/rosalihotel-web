-- ============================================================
-- Rosali Hotel — full database schema + bootstrap seed data
-- ============================================================
-- One-shot import for setting up a fresh rosalihotel database
-- (e.g. when deploying to a new server). Safe to re-run on an
-- existing database: every CREATE uses IF NOT EXISTS and every
-- INSERT uses IGNORE / ON DUPLICATE KEY UPDATE.
--
-- Usage (cPanel phpMyAdmin):
--   1. Create database "rosalihotel" (UTF-8, utf8mb4 collation)
--   2. Open it, go to the SQL tab, paste this file, click Go.
--
-- Usage (CLI):
--   mysql -u USER -p DBNAME < sql/rosalihotel.sql
--
-- Default admin login (CHANGE PASSWORD AFTER FIRST LOGIN):
--   username: admin
--   password: Admin@Rosali123
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- Core tables
-- ============================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(80) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `key`   VARCHAR(100) NOT NULL,
  `value` TEXT DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `rooms` (
  `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(120) NOT NULL,
  `description`  TEXT NOT NULL,
  `price`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `capacity`     TINYINT(3) UNSIGNED NOT NULL DEFAULT 2,
  `photo`        VARCHAR(255) NOT NULL DEFAULT '',
  `is_available` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(160) NOT NULL,
  `photo`      VARCHAR(255) NOT NULL DEFAULT '',
  `category`   VARCHAR(80) NOT NULL DEFAULT '',
  `sort_order` SMALLINT(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_sort`     (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `events` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(160) NOT NULL,
  `description` TEXT NOT NULL,
  `event_date`  DATE NOT NULL,
  `photo`       VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cafe_menu` (
  `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120) NOT NULL,
  `description` TEXT NOT NULL,
  `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `photo`       VARCHAR(255) NOT NULL DEFAULT '',
  `category`    VARCHAR(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(160) NOT NULL DEFAULT '',
  `phone`      VARCHAR(40)  NOT NULL DEFAULT '',
  `message`    TEXT NOT NULL,
  `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_read`    (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `media` (
  `id`              INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename`        VARCHAR(255) NOT NULL,
  `original_name`   VARCHAR(255) NOT NULL,
  `file_type`       ENUM('image','video','splat') NOT NULL,
  `mime_type`       VARCHAR(100) NOT NULL DEFAULT '',
  `file_size_bytes` BIGINT(20) NOT NULL DEFAULT 0,
  `category`        VARCHAR(50)  NOT NULL DEFAULT 'general',
  `assigned_to`     VARCHAR(150) NOT NULL DEFAULT '',
  `is_published`    TINYINT(1) NOT NULL DEFAULT 1,
  `uploaded_by`     INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_file_type`    (`file_type`),
  KEY `idx_category`     (`category`),
  KEY `idx_assigned_to`  (`assigned_to`),
  KEY `idx_is_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Analytics tables (visitor tracking)
-- ============================================================

CREATE TABLE IF NOT EXISTS `visitor_logs` (
  `id`          BIGINT AUTO_INCREMENT PRIMARY KEY,
  `page`        VARCHAR(100) NOT NULL,
  `ip_address`  VARCHAR(45) NOT NULL,
  `country`     VARCHAR(100) NULL,
  `city`        VARCHAR(100) NULL,
  `device_type` VARCHAR(20)  NULL,
  `browser`     VARCHAR(50)  NULL,
  `os`          VARCHAR(50)  NULL,
  `referrer`    VARCHAR(500) NULL,
  `visit_date`  DATE NOT NULL,
  `visit_time`  TIME NOT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_visit_date_page` (`visit_date`, `page`),
  INDEX `idx_ip_address`      (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `visitor_daily_summary` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `page`          VARCHAR(100) NOT NULL,
  `visit_date`    DATE NOT NULL,
  `total_visits`  INT DEFAULT 0,
  `unique_visits` INT DEFAULT 0,
  UNIQUE KEY `unique_page_date` (`page`, `visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `visitor_ip_cache` (
  `ip_address` VARCHAR(45) PRIMARY KEY,
  `country`    VARCHAR(100) NULL,
  `city`       VARCHAR(100) NULL,
  `cached_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Bootstrap seed data
-- ============================================================
-- Default admin user — password is Admin@Rosali123, hashed with
-- PASSWORD_BCRYPT (cost 10). CHANGE ME after first login.
INSERT IGNORE INTO `users` (`id`, `username`, `password`)
VALUES (1, 'admin', '$2y$10$Ov7ZfRQBbJQ.8yB7cm/u6uJVVIvhLEZdlXvGAMRl/WOLgGOC6u6rG');

-- Default site settings. ON DUPLICATE KEY UPDATE is a no-op so
-- re-running won't overwrite a server's existing values.
INSERT INTO `settings` (`key`, `value`) VALUES
  ('active_theme',           'rosa'),
  ('active_lang',            'id'),
  ('splat_enabled',          '0'),
  ('geo_local_enabled',      '0'),
  ('page_visibility',        '{"home":true,"rooms":true,"events":true,"cafe":true,"gallery":true,"tourism":true,"contact":true}'),
  ('rosali_color_overrides', '{}')
ON DUPLICATE KEY UPDATE `key` = `key`;

SET FOREIGN_KEY_CHECKS = 1;
