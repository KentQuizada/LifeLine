<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_requests', function (Blueprint $table) {
            $table->id();
            // Format LL-YYMMDD-XXXXXX; UNIQUE is the final guard against duplicates (DEC-006).
            $table->string('reference_number', 16)->unique();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('hospital_id')->constrained();
            $table->string('patient_name', 150);
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->unsignedTinyInteger('units');
            $table->enum('urgency', ['critical', 'urgent']);
            $table->enum('status', ['open', 'donor_found', 'fulfilled', 'cancelled', 'expired'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        DB::statement('ALTER TABLE blood_requests ADD CONSTRAINT chk_blood_requests_units CHECK (units BETWEEN 1 AND 10)');
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_requests');
    }
};
