-- migrations_plain: 2026-10-24
-- Plain SQL for all dashboard migrations created on this date.
-- For reference and manual application only; run via `php artisan migrate` in practice.

CREATE TABLE `feature_toggles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('minigame','modifier') NOT NULL,
  `key` VARCHAR(64) NOT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `reason` TEXT NULL,
  `issue_id` BIGINT UNSIGNED NULL,
  `changed_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  UNIQUE KEY `feature_toggles_type_key_unique` (`type`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `announcements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `message` TEXT NOT NULL,
  `type` ENUM('info','warning','maintenance') NOT NULL,
  `starts_at` DATETIME NULL,
  `ends_at` DATETIME NULL,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `support_messages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `message` TEXT NOT NULL,
  `type` ENUM('feedback','bug','support') NOT NULL DEFAULT 'support',
  `status` ENUM('new','in_progress','waiting','resolved') NOT NULL DEFAULT 'new',
  `user_id` INT UNSIGNED NULL,
  `game_id` BIGINT UNSIGNED NULL,
  `device_id` VARCHAR(128) NULL,
  `assignee_id` INT UNSIGNED NULL,
  `staff_note` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `support_messages_user_id_index` (`user_id`),
  KEY `support_messages_game_id_index` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `dash_internal_notes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `note_type` VARCHAR(64) NOT NULL,
  `note_id` BIGINT UNSIGNED NOT NULL,
  `note` TEXT NOT NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `dash_internal_notes_note_type_note_id_index` (`note_type`, `note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
