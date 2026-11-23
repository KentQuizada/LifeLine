<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** DEC-026: a donor without a shared location can be matched; their distance is unknown (NULL). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_matches', function (Blueprint $table) {
            $table->decimal('distance_km', 6, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Rows with an unknown distance cannot be NOT NULL again; give them 0 first.
        DB::table('donor_matches')->whereNull('distance_km')->update(['distance_km' => 0]);

        Schema::table('donor_matches', function (Blueprint $table) {
            $table->decimal('distance_km', 6, 2)->nullable(false)->change();
        });
    }
};
