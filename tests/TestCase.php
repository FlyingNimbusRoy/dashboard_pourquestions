<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /** The only database tests may wipe (RefreshDatabase runs migrate:fresh). */
    private const TEST_DATABASE = 'pourquestions_testing';

    /**
     * Runs before RefreshDatabase wipes anything. A cached config (php artisan optimize /
     * config:cache) makes tests ignore phpunit.xml and point at the local data copy — that
     * wiped it once (2026-10-16). Refuse instead of wiping.
     */
    protected function setUpTraits()
    {
        if (isset(class_uses_recursive(static::class)[RefreshDatabase::class])) {
            $database = DB::connection()->getDatabaseName();

            if ($database !== self::TEST_DATABASE) {
                throw new RuntimeException("Refusing to refresh database [{$database}]: tests may only wipe [".self::TEST_DATABASE.']. Run `php artisan config:clear` (never cache config in development).');
            }
        }

        return parent::setUpTraits();
    }
}
