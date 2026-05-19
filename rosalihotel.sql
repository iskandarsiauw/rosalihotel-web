-- Rosali Hotel — Database Schema
-- MySQL 5.7+ / MariaDB 10.3+
-- Character set: utf8mb4 | Engine: InnoDB

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ─────────────────────────────────────────
-- Create database (run once if needed)
-- ─────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `rosalihotel`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `rosalihotel`;

-- ─────────────────────────────────────────
-- Table: users
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(80)      NOT NULL UNIQUE,
  `password`   VARCHAR(255)     NOT NULL,
  `created_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Table: rooms
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `rooms` (
  `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`         VARCHAR(120)     NOT NULL,
  `description`  TEXT             NOT NULL,
  `price`        DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
  `capacity`     TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `photo`        VARCHAR(255)     NOT NULL DEFAULT '',
  `is_available` TINYINT(1)       NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Table: gallery (legacy — kept for compat; new content uses `media`)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `gallery` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `title`      VARCHAR(160)  NOT NULL,
  `photo`      VARCHAR(255)  NOT NULL DEFAULT '',
  `category`   VARCHAR(80)   NOT NULL DEFAULT '',
  `sort_order` SMALLINT      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_sort`     (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Table: events
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `events` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(160) NOT NULL,
  `description` TEXT         NOT NULL,
  `event_date`  DATE         NOT NULL,
  `photo`       VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Table: cafe_menu
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `cafe_menu` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(120)  NOT NULL,
  `description` TEXT          NOT NULL,
  `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `photo`       VARCHAR(255)  NOT NULL DEFAULT '',
  `category`    VARCHAR(80)   NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Table: messages
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(160) NOT NULL DEFAULT '',
  `phone`      VARCHAR(40)  NOT NULL DEFAULT '',
  `message`    TEXT         NOT NULL,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_read`    (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────
-- Table: media (unified media library — Phase 5)
-- file_type: image | video | splat
-- category : rooms | gallery | events | cafe | general | room_tour
-- assigned_to: free-text slot/owner — e.g. 'slot:hero___garden_entrance',
--              'room:3:cover', 'room:3:video', 'room:3:splat', 'gallery'
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `media` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename`        VARCHAR(255) NOT NULL,
  `original_name`   VARCHAR(255) NOT NULL,
  `file_type`       ENUM('image','video','splat') NOT NULL,
  `mime_type`       VARCHAR(100) NOT NULL DEFAULT '',
  `file_size_bytes` BIGINT       NOT NULL DEFAULT 0,
  `category`        VARCHAR(50)  NOT NULL DEFAULT 'general',
  `assigned_to`     VARCHAR(150) NOT NULL DEFAULT '',
  `is_published`    TINYINT(1)   NOT NULL DEFAULT 1,
  `uploaded_by`     INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_file_type`    (`file_type`),
  KEY `idx_category`     (`category`),
  KEY `idx_assigned_to`  (`assigned_to`),
  KEY `idx_is_published` (`is_published`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────
-- Table: settings
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `settings` (
  `key`   VARCHAR(100) NOT NULL,
  `value` MEDIUMTEXT,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`, `value`) VALUES
  ('active_theme',   'rosa'),
  ('active_lang',    'id'),
  ('splat_enabled',  '0'),
  ('page_visibility', '{"home":true,"rooms":true,"events":true,"cafe":true,"gallery":true,"tourism":true,"contact":true}')
ON DUPLICATE KEY UPDATE `value` = `value`;

-- ─────────────────────────────────────────
-- Seed: default admin user
-- username : admin
-- password : Admin@Rosali123  (bcrypt)
-- ─────────────────────────────────────────
INSERT IGNORE INTO `users` (`username`, `password`) VALUES (
  'admin',
  '$2y$10$Ov7ZfRQBbJQ.8yB7cm/u6uJVVIvhLEZdlXvGAMRl/WOLgGOC6u6rG'
);
