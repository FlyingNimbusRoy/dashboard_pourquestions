<?php

return [

    'difficulty' => [
        'min' => 1,
        'max' => 5,
    ],

    'similarity' => [
        // Pairs scoring at or above this are stored for review (0..1).
        // Text language of the questions: english or dutch.
        'language' => env('SIMILARITY_LANGUAGE', 'english'),
        // A question text occurring this often is a template; its pairs are judged on the answers.
        'template_min_occurrences' => 3,
        // Template pairs whose answer options overlap at least this much (Jaccard) are duplicates.
        'template_options_overlap' => 0.5,
        'store_threshold' => (float) env('SIMILARITY_STORE_THRESHOLD', 0.6),
        // Pairs at or above this are labelled "likely duplicate".
        'duplicate_threshold' => (float) env('SIMILARITY_DUPLICATE_THRESHOLD', 0.72),
        // Upper bound of candidates scored per question; keeps a run near-linear.
        'max_candidates' => 400,
        // Stems that appear in more than this share of questions are too common to find candidates with.
        'max_document_frequency' => 0.05,
        // How long the in-memory corpus used for live "possible duplicate" hints is cached.
        'corpus_cache_seconds' => 600,
    ],

    'validation' => [
        'revalidate_after_days' => (int) env('VALIDATION_REVALIDATE_AFTER_DAYS', 365),
        'batch_size' => (int) env('VALIDATION_BATCH_SIZE', 10),
        'batch_due_days' => (int) env('VALIDATION_BATCH_DUE_DAYS', 7),
        // The scheduler stops creating batches while this many are still open.
        'max_open_batches' => (int) env('VALIDATION_MAX_OPEN_BATCHES', 5),
    ],

    'quota' => [
        'monthly_target' => (int) env('QUOTA_MONTHLY_TARGET', 50),
    ],

    'games' => [
        // A game not finished/cancelled and older than this many hours counts as stale.
        'stale_after_hours' => (int) env('GAMES_STALE_AFTER_HOURS', 6),
    ],

    'sockets' => [
        // Who must own a gamepack for its content to be playable: 'host' = host owns -> whole room plays (D-006).
        // 'all' = every player in the room must own it (resolver then needs the players; not implemented yet).
        'ownership' => env('SOCKETS_OWNERSHIP', 'host'),
        'registry_file' => database_path('data/socket-registry.json'),
    ],

    'insights' => [
        'cache_seconds' => 600,
        'top_questions' => 25,
        'chart_color' => '#a855f7',
        // Questions asked this many times or more are flagged as retirement candidates.
        'retirement_threshold' => (int) env('INSIGHTS_RETIREMENT_THRESHOLD', 50),
    ],

    // Shared hosting has no long-running queue worker: let the scheduler (cron) drain the queue.
    'queue_via_scheduler' => (bool) env('QUEUE_VIA_SCHEDULER', false),

    'ingest' => [
        // Shared secret sent by the sockets server in the X-Ingest-Secret header.
        'secret' => env('INGEST_SECRET'),
    ],

    'analytics' => [
        // Minimum game count before a question is eligible for threshold flagging.
        'min_samples' => (int) env('ANALYTICS_MIN_SAMPLES', 10),
        'thresholds' => [
            'low_correct_rate'  => (float) env('ANALYTICS_LOW_CORRECT_RATE',  0.20),
            'high_correct_rate' => (float) env('ANALYTICS_HIGH_CORRECT_RATE', 0.95),
            'high_timeout_rate' => (float) env('ANALYTICS_HIGH_TIMEOUT_RATE', 0.40),
        ],
    ],

    'import' => [
        // Tables copied from the live database, in dependency order: every game table except
        // Laravel internals (cache, jobs, sessions, password_reset_tokens, migrations) and `online`
        // (live connection tracking). `users` is sanitized on the way in (see SourceImporter).
        'tables' => [
            'users', 'categories', 'gamepacks', 'characters', 'achievements', 'modifiers',
            'questions', 'answers', 'question_similarities',
            'games', 'game_presets', 'setup_presets', 'gamepack_purchased',
            'booze_buddies', 'comments', 'user_achievements', 'user_gamestats',
            'question_game_results', 'player_game_log',
        ],
        'chunk' => 1000,
        // Scratch database used by `dashboard:import-dump`, and the MariaDB root login used to create it.
        'scratch_database' => 'pourquestions_import',
        'root_username' => env('DB_ROOT_USERNAME', 'root'),
        'root_password' => env('DB_ROOT_PASSWORD', 'root'),
    ],

];
