-- migrations_plain: 2026-10-22
-- Plain SQL for all dashboard migrations created on this date.
-- For reference and manual application only; run via `php artisan migrate` in practice.

CREATE TABLE `dash_game_sessions` (
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


CREATE TABLE `dash_game_question_results` (
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
  `answer_ms` JSON NULL,
  `avg_answer_ms` SMALLINT UNSIGNED NULL,
  KEY `dash_game_question_results_game_session_id_index` (`game_session_id`),
  KEY `dash_game_question_results_question_id_index` (`question_id`),
  KEY `dash_game_question_results_category_id_index` (`category_id`),
  CONSTRAINT `dgqr_game_session_id_fk`
    FOREIGN KEY (`game_session_id`) REFERENCES `dash_game_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `dash_live_snapshots` (
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


CREATE TABLE `dash_rollup_daily` (
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


CREATE TABLE `dash_issues` (
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


CREATE TABLE `dash_issue_activity` (
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


-- 2026_10_22_000007: add version tracking to questions
ALTER TABLE `questions`
  ADD COLUMN `version` SMALLINT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`;
