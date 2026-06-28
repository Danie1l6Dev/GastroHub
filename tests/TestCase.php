<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureTestsUseDisposableDatabase();
    }

    private function ensureTestsUseDisposableDatabase(): void
    {
        if (! $this->app->environment('testing')) {
            throw new RuntimeException('Tests must run with APP_ENV=testing.');
        }

        if (config('database.default') !== 'sqlite' || config('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('Tests must run against the in-memory SQLite database.');
        }
    }
}
