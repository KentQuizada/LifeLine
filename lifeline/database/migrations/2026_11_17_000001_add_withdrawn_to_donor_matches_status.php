<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A donor who accepted can withdraw before a donation is recorded (M3.2 follow-up, DEC-022). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_matches', function (Blueprint $table) {
            $table->enum('status', ['notified', 'accepted', 'declined', 'withdrawn'])->default('notified')->change();
        });
    }

    public function down(): void
    {
        // Withdrawn answers become declined: the closest earlier meaning.
        DB::table('donor_matches')->where('status', 'withdrawn')->update(['status' => 'declined']);

        Schema::table('donor_matches', function (Blueprint $table) {
            $table->enum('status', ['notified', 'accepted', 'declined'])->default('notified')->change();
        });
    }
};
