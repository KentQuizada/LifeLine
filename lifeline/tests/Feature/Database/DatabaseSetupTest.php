<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_mariadb_test_database(): void
    {
        $this->assertSame('mariadb', DB::connection()->getDriverName());
        $this->assertSame('lifeline_test', DB::connection()->getDatabaseName());
    }

    public function test_only_needed_framework_tables_exist(): void
    {
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('password_reset_tokens'));

        foreach (['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "{$table} should not exist");
        }
    }
}
