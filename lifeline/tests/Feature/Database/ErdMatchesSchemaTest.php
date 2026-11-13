<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ErdMatchesSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_erd_lists_every_table_and_column(): void
    {
        $erdPath = base_path('../docs/database/erd.md');
        $this->assertFileExists($erdPath);

        preg_match_all('/^ {4}(\w+) \{\R(.*?)\R {4}\}/ms', file_get_contents($erdPath), $blocks, PREG_SET_ORDER);

        $documented = [];
        foreach ($blocks as [, $table, $body]) {
            preg_match_all('/^\s+\S+ (\w+)/m', $body, $columns);
            $documented[$table] = $columns[1];
        }

        // Only this database's tables (XAMPP may hold other databases with similar names).
        $tables = collect(DB::select(
            "SELECT TABLE_NAME AS name FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE' AND TABLE_NAME <> 'migrations'"
        ))->pluck('name')->all();

        $this->assertEqualsCanonicalizing($tables, array_keys($documented));

        foreach ($tables as $table) {
            $this->assertEqualsCanonicalizing(
                Schema::getColumnListing($table),
                $documented[$table],
                "ERD columns differ for {$table}"
            );
        }
    }
}
