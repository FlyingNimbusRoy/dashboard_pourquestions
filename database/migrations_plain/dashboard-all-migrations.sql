-- =============================================================================
-- Pour Questions Dashboard — all migrations in one file
-- Safe to run on a fresh database that already has the game schema.
-- All tables use CREATE TABLE IF NOT EXISTS; ALTER columns use IF NOT EXISTS.
-- Drops for removed features (dash_pending_live_changes, answer_ms) are
-- omitted — they only matter on a Docker dev DB that ran earlier migrations.
-- =============================================================================


-- -----------------------------------------------------------------------------
-- 1. Similarity tracking (2026-10-16)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dash_question_similarities` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT UNSIGNED NOT NULL,
  `similar_question_id` INT UNSIGNED NOT NULL,
  `score` FLOAT NOT NULL,
  `signals` JSON NOT NULL,
  `status` VARCHAR(16) NOT NULL DEFAULT 'open',
  `reviewed_by` BIGINT UNSIGNED NULL,
  `reviewed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  UNIQUE KEY `dash_question_similarities_pair_unique` (`question_id`, `similar_question_id`),
  KEY `dash_question_similarities_status_index` (`status`),
  CONSTRAINT `dqs_question_id_fk`
    FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dqs_similar_question_id_fk`
    FOREIGN KEY (`similar_question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dqs_reviewed_by_fk`
    FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 2. Review batches + reviews (2026-10-16)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dash_review_batches` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `reviewer_id` BIGINT UNSIGNED NULL,
  `due_at` DATE NULL,
  `completed_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `dash_review_batches_completed_at_index` (`completed_at`),
  CONSTRAINT `drb_reviewer_id_fk`
    FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dash_question_reviews` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `review_batch_id` BIGINT UNSIGNED NOT NULL,
  `question_id` INT UNSIGNED NOT NULL,
  `reviewer_id` BIGINT UNSIGNED NULL,
  `outcome` VARCHAR(16) NOT NULL DEFAULT 'pending',
  `difficulty_before` TINYINT UNSIGNED NOT NULL,
  `difficulty_rating` TINYINT UNSIGNED NULL,
  `answers_correct` TINYINT(1) NULL,
  `wording_clear` TINYINT(1) NULL,
  `category_correct` TINYINT(1) NULL,
  `notes` TEXT NULL,
  `reason` VARCHAR(24) NULL,
  `reviewed_at` TIMESTAMP NULL,
  `resolved_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  UNIQUE KEY `dash_question_reviews_batch_question_unique` (`review_batch_id`, `question_id`),
  KEY `dash_question_reviews_outcome_index` (`outcome`),
  CONSTRAINT `dqr_review_batch_id_fk`
    FOREIGN KEY (`review_batch_id`) REFERENCES `dash_review_batches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dqr_question_id_fk`
    FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dqr_reviewer_id_fk`
    FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 3. Question change audit trail (2026-10-16)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dash_question_changes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `action` VARCHAR(32) NOT NULL,
  `changes` JSON NOT NULL,
  `created_at` TIMESTAMP NULL,
  KEY `dash_question_changes_question_id_index` (`question_id`),
  KEY `dash_question_changes_created_at_index` (`created_at`),
  CONSTRAINT `dqc_user_id_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 4. Minigames + modifier settings (2026-10-16)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dash_minigames` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(64) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `gamepack_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `min_players` SMALLINT UNSIGNED NULL,
  `max_players` SMALLINT UNSIGNED NULL,
  `settings` JSON NULL,
  `sort` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  UNIQUE KEY `dash_minigames_key_unique` (`key`),
  KEY `dash_minigames_gamepack_id_index` (`gamepack_id`),
  CONSTRAINT `dm_gamepack_id_fk`
    FOREIGN KEY (`gamepack_id`) REFERENCES `gamepacks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dash_modifier_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(64) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `turnbased` TINYINT(1) NOT NULL DEFAULT 0,
  `modifier_id` INT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `gamepack_id` INT UNSIGNED NULL,
  `settings` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  UNIQUE KEY `dash_modifier_settings_key_unique` (`key`),
  KEY `dash_modifier_settings_modifier_id_index` (`modifier_id`),
  KEY `dash_modifier_settings_gamepack_id_index` (`gamepack_id`),
  CONSTRAINT `dms_modifier_id_fk`
    FOREIGN KEY (`modifier_id`) REFERENCES `modifiers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `dms_gamepack_id_fk`
    FOREIGN KEY (`gamepack_id`) REFERENCES `gamepacks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 5. Game session ingest (2026-10-22)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dash_game_sessions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT UNSIGNED NOT NULL,
  `pin` VARCHAR(10) NOT NULL,
  `host_user_id` INT UNSIGNED NULL,
  `host_device_id` VARCHAR(64) NULL,
  `status` ENUM('completed','ended_early','abandoned') NOT NULL,
  `schema_version` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `app_version` VARCHAR(20) NOT NULL,
  `player_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `question_count` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `started_at` DATETIME NULL,
  `ended_at` DATETIME NULL,
  `duration_s` INT UNSIGNED NULL,
  `lead_changes` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `last_phase_type` VARCHAR(20) NULL,
  `last_phase_round` TINYINT UNSIGNED NULL,
  `settings` JSON NOT NULL,
  `payload` JSON NOT NULL,
  `ingested_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `dash_game_sessions_game_id_unique` (`game_id`),
  KEY `dash_game_sessions_host_user_id_index` (`host_user_id`),
  KEY `dash_game_sessions_started_at_index` (`started_at`),
  KEY `dash_game_sessions_status_index` (`status`),
  KEY `dash_game_sessions_app_version_index` (`app_version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- answer_ms and avg_answer_ms are intentionally omitted (dropped by 2026-10-24 migration)
CREATE TABLE IF NOT EXISTS `dash_game_question_results` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `game_session_id` BIGINT UNSIGNED NOT NULL,
  `question_id` INT UNSIGNED NOT NULL,
  `question_version` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `category_id` INT UNSIGNED NULL,
  `round` TINYINT UNSIGNED NOT NULL,
  `correct_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `wrong_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `timeout_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `option_counts` JSON NULL,
  KEY `dash_game_question_results_game_session_id_index` (`game_session_id`),
  KEY `dash_game_question_results_question_id_index` (`question_id`),
  KEY `dash_game_question_results_category_id_index` (`category_id`),
  CONSTRAINT `dgqr_game_session_id_fk`
    FOREIGN KEY (`game_session_id`) REFERENCES `dash_game_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 6. Live snapshots + daily rollup (2026-10-22)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dash_live_snapshots` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `open_rooms` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `players_online` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `games_today` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `rooms_per_phase` JSON NULL,
  `failed_post_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `retry_queue_size` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `snapshot_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `dash_live_snapshots_snapshot_at_index` (`snapshot_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dash_rollup_daily` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `date` DATE NOT NULL,
  `games_total` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `games_completed` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `games_ended_early` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `games_abandoned` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `unique_hosts` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `total_player_sessions` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `avg_duration_s` INT UNSIGNED NULL,
  `replay_rate` DECIMAL(5,4) NULL,
  `completion_rate` DECIMAL(5,4) NULL,
  `computed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `dash_rollup_daily_date_unique` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 7. Issue tracking (2026-10-22)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dash_issues` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `type` ENUM('content','balance','tech') NOT NULL,
  `subject_type` VARCHAR(30) NOT NULL,
  `subject_id` INT UNSIGNED NULL,
  `reason_code` VARCHAR(50) NOT NULL,
  `reason_summary` TEXT NOT NULL,
  `metric_snapshot` JSON NULL,
  `status` ENUM('open','assigned','in_progress','resolved','wont_fix') NOT NULL DEFAULT 'open',
  `assignee_id` INT UNSIGNED NULL,
  `created_by` INT UNSIGNED NULL,
  `resolved_at` DATETIME NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  KEY `dash_issues_reason_code_index` (`reason_code`),
  KEY `dash_issues_assignee_id_index` (`assignee_id`),
  KEY `dash_issues_type_status_index` (`type`, `status`),
  KEY `dash_issues_subject_type_subject_id_index` (`subject_type`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dash_issue_activity` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `issue_id` BIGINT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(30) NOT NULL,
  `from_value` VARCHAR(50) NULL,
  `to_value` VARCHAR(50) NULL,
  `note` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `dash_issue_activity_issue_id_index` (`issue_id`),
  CONSTRAINT `dia_issue_id_fk`
    FOREIGN KEY (`issue_id`) REFERENCES `dash_issues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -----------------------------------------------------------------------------
-- 8. Add version tracking to questions (2026-10-22)
-- -----------------------------------------------------------------------------
ALTER TABLE `questions`
  ADD COLUMN IF NOT EXISTS `version` SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`;


-- -----------------------------------------------------------------------------
-- 9. Operational tables (2026-10-24)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `feature_toggles` (
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

CREATE TABLE IF NOT EXISTS `announcements` (
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

CREATE TABLE IF NOT EXISTS `support_messages` (
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

CREATE TABLE IF NOT EXISTS `dash_internal_notes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `note_type` VARCHAR(64) NOT NULL,
  `note_id` BIGINT UNSIGNED NOT NULL,
  `note` TEXT NOT NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `dash_internal_notes_note_type_note_id_index` (`note_type`, `note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
