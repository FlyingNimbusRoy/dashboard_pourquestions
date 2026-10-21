SET FOREIGN_KEY_CHECKS=0;
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Gegenereerd op: 16 okt 2026 om 11:55
-- Serverversie: 10.6.22-MariaDB-cll-lve
-- PHP-versie: 8.1.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `t7x607p264w_pourquestions`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `achievements`
--

CREATE TABLE `achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `criteria_type` enum('games_played','games_won','total_shots','shot_highscore','single_game_shots','member_number') NOT NULL,
  `criteria_value` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `exact_match` tinyint(1) NOT NULL DEFAULT 0,
  `character_id` int(10) UNSIGNED DEFAULT NULL,
  `available_from` timestamp NULL DEFAULT NULL,
  `available_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `answers`
--

CREATE TABLE `answers` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `answer` varchar(255) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `booze_buddies`
--

CREATE TABLE `booze_buddies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','accepted','declined') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `characters`
--

CREATE TABLE `characters` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gamepack_id` int(10) UNSIGNED DEFAULT NULL,
  `color_primary` varchar(255) NOT NULL DEFAULT '#6F1E51',
  `color_secondary` varchar(255) NOT NULL DEFAULT '#1B1464',
  `color_muted_primary` varchar(255) NOT NULL DEFAULT '#ED4C67',
  `color_muted_secondary` varchar(255) NOT NULL DEFAULT '#B53471',
  `show_on_homepage` tinyint(1) NOT NULL DEFAULT 1,
  `parent_character_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `comments`
--

CREATE TABLE `comments` (
  `id` int(10) UNSIGNED NOT NULL,
  `comment` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `gamepacks`
--

CREATE TABLE `gamepacks` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('gamepack','questionpack','skinpack') NOT NULL DEFAULT 'gamepack',
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `url_coverart` varchar(255) DEFAULT 'none',
  `flavor_text` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `gamepack_purchased`
--

CREATE TABLE `gamepack_purchased` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `gamepack_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `opened` tinyint(1) NOT NULL DEFAULT 0,
  `access_through_admin_grant` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `games`
--

CREATE TABLE `games` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `shotstaken` int(11) NOT NULL DEFAULT 0,
  `players` int(11) DEFAULT NULL,
  `game_length` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_nsfw` tinyint(1) NOT NULL DEFAULT 0,
  `is_finished` tinyint(1) NOT NULL DEFAULT 0,
  `is_cancelled` tinyint(1) NOT NULL DEFAULT 0,
  `host_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sAndb` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '0',
  `question_array` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`question_array`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `game_presets`
--

CREATE TABLE `game_presets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `rounds` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `is_nsfw` tinyint(1) NOT NULL DEFAULT 0,
  `pack_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`pack_ids`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `modifiers`
--

CREATE TABLE `modifiers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(500) DEFAULT NULL,
  `turnbased` tinyint(1) NOT NULL DEFAULT 0,
  `effects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`effects`)),
  `coupled_gamepack_id` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `online`
--

CREATE TABLE `online` (
  `id` int(10) UNSIGNED NOT NULL,
  `game_id` int(10) UNSIGNED DEFAULT NULL,
  `host` tinyint(1) NOT NULL DEFAULT 0,
  `user_csrf` varchar(255) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `questions`
--

CREATE TABLE `questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `maker_id` int(10) UNSIGNED NOT NULL,
  `vraag` varchar(255) NOT NULL,
  `trivia` varchar(255) DEFAULT NULL,
  `is_random` tinyint(1) NOT NULL DEFAULT 0,
  `is_nsfw` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_compared_at` timestamp NULL DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `gamepack_id` int(10) UNSIGNED DEFAULT NULL,
  `difficulty` int(11) NOT NULL DEFAULT 3,
  `last_validated` timestamp NOT NULL DEFAULT '2020-12-31 23:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `question_similarities`
--

CREATE TABLE `question_similarities` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `similar_question_id` int(10) UNSIGNED NOT NULL,
  `similarity_score` float NOT NULL,
  `handled` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `setup_presets`
--

CREATE TABLE `setup_presets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(24) NOT NULL,
  `game_length` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `is_nsfw` tinyint(1) NOT NULL DEFAULT 1,
  `burst_mode` tinyint(1) NOT NULL DEFAULT 0,
  `excluded_packs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`excluded_packs`)),
  `excluded_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`excluded_categories`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user_achievements`
--

CREATE TABLE `user_achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `achievement_id` bigint(20) UNSIGNED NOT NULL,
  `unlocked_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user_gamestats`
--

CREATE TABLE `user_gamestats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `games_played` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `games_won` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `games_lost` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_shots` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `most_shots` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `least_shots` int(10) UNSIGNED DEFAULT NULL,
  `shot_highscore` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `question_highscore` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `modifiers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`modifiers`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_achievements_character` (`character_id`);

--
-- Indexen voor tabel `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answers_question_id_foreign` (`question_id`);

--
-- Indexen voor tabel `booze_buddies`
--
ALTER TABLE `booze_buddies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexen voor tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexen voor tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexen voor tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `characters_gamepack_id_foreign` (`gamepack_id`),
  ADD KEY `characters_parent_character_id_foreign` (`parent_character_id`);

--
-- Indexen voor tabel `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexen voor tabel `gamepacks`
--
ALTER TABLE `gamepacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `gamepack_purchased`
--
ALTER TABLE `gamepack_purchased`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gamepack_purchased_gamepack_id_foreign` (`gamepack_id`),
  ADD KEY `gamepack_purchased_user_id_foreign` (`user_id`);

--
-- Indexen voor tabel `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `games_host_id_index` (`host_id`);

--
-- Indexen voor tabel `game_presets`
--
ALTER TABLE `game_presets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_presets_user_id_foreign` (`user_id`);

--
-- Indexen voor tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexen voor tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `modifiers`
--
ALTER TABLE `modifiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modifiers_coupled_gamepack_id_foreign` (`coupled_gamepack_id`);

--
-- Indexen voor tabel `online`
--
ALTER TABLE `online`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexen voor tabel `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_maker_id_foreign` (`maker_id`),
  ADD KEY `questions_category_id_foreign` (`category_id`),
  ADD KEY `questions_gamepack_id_foreign` (`gamepack_id`);

--
-- Indexen voor tabel `question_similarities`
--
ALTER TABLE `question_similarities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_question_similarity` (`question_id`,`similar_question_id`),
  ADD KEY `fk_question_similarities_similar_question` (`similar_question_id`);

--
-- Indexen voor tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexen voor tabel `setup_presets`
--
ALTER TABLE `setup_presets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `setup_presets_user_id_index` (`user_id`);

--
-- Indexen voor tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexen voor tabel `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Indexen voor tabel `user_gamestats`
--
ALTER TABLE `user_gamestats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_gamestats` (`user_id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `booze_buddies`
--
ALTER TABLE `booze_buddies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `characters`
--
ALTER TABLE `characters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `gamepacks`
--
ALTER TABLE `gamepacks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `gamepack_purchased`
--
ALTER TABLE `gamepack_purchased`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `games`
--
ALTER TABLE `games`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `game_presets`
--
ALTER TABLE `game_presets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `modifiers`
--
ALTER TABLE `modifiers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `online`
--
ALTER TABLE `online`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `question_similarities`
--
ALTER TABLE `question_similarities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `setup_presets`
--
ALTER TABLE `setup_presets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `user_gamestats`
--
ALTER TABLE `user_gamestats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `fk_achievements_character` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`) ON DELETE SET NULL;

--
-- Beperkingen voor tabel `booze_buddies`
--
ALTER TABLE `booze_buddies`
  ADD CONSTRAINT `booze_buddies_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booze_buddies_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `game_presets`
--
ALTER TABLE `game_presets`
  ADD CONSTRAINT `game_presets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `modifiers`
--
ALTER TABLE `modifiers`
  ADD CONSTRAINT `modifiers_coupled_gamepack_id_foreign` FOREIGN KEY (`coupled_gamepack_id`) REFERENCES `gamepacks` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `question_similarities`
--
ALTER TABLE `question_similarities`
  ADD CONSTRAINT `fk_question_similarities_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_question_similarities_similar_question` FOREIGN KEY (`similar_question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `user_gamestats`
--
ALTER TABLE `user_gamestats`
  ADD CONSTRAINT `user_gamestats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
INSERT INTO `categories` (`id`,`name`) VALUES (1,'Geschiedenis'),(2,'Sport');
INSERT INTO `gamepacks` (`id`,`name`,`category`) VALUES (1,'Basis','gamepack');
INSERT INTO `characters` (`id`,`name`,`url`,`gamepack_id`) VALUES (1,'Piet','piet.png',1);
INSERT INTO `users` (`id`,`name`,`email`,`password`,`remember_token`,`created_at`,`is_admin`) VALUES (1,'Anna','anna@example.com','$2y$10$secrethash','tok123','2025-01-01 10:00:00',1),(2,'Bob','bob@example.com','$2y$10$secrethash2',NULL,'2025-02-01 10:00:00',0),(3,'Cleo','cleo@example.com','x','abc',NULL,0);
INSERT INTO `questions` (`id`,`maker_id`,`vraag`,`category_id`,`gamepack_id`,`difficulty`) VALUES (1,1,'Wie won het WK 2010?',2,1,2),(2,2,'In welk jaar begon WO2?',1,1,3),(3,1,'Wat is de hoofdstad van Frankrijk?',1,1,1);
INSERT INTO `answers` (`id`,`question_id`,`answer`,`is_correct`) VALUES (1,1,'Spanje',1),(2,1,'Nederland',0),(3,2,'1939',1),(4,2,'1914',0),(5,3,'Parijs',1),(6,3,'Lyon',0);
INSERT INTO `games` (`id`,`name`,`host_id`,`question_array`) VALUES (1,'Vrijdagavond',1,'[1,2,3]'),(2,'Zondag',2,NULL);
INSERT INTO `user_gamestats` (`id`,`user_id`,`games_played`) VALUES (1,1,5),(2,2,3);
INSERT INTO `online` (`id`,`game_id`,`user_csrf`) VALUES (1,1,'csrf-secret');
