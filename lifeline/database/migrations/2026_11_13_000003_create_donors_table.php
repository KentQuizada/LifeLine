<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']);
            $table->date('birthdate');
            $table->decimal('weight_kg', 5, 2);
            $table->string('city', 100);
            // 2 decimal places = about 1 km: exact home locations cannot be stored (DEC-004).
            $table->decimal('approx_latitude', 5, 2)->nullable();
            $table->decimal('approx_longitude', 5, 2)->nullable();
            $table->boolean('location_sharing')->default(false);
            $table->boolean('is_available')->default(true);
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['blood_type', 'is_available']);
        });

        DB::statement('ALTER TABLE donors ADD CONSTRAINT chk_donors_weight CHECK (weight_kg > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('donors');
    }
};
