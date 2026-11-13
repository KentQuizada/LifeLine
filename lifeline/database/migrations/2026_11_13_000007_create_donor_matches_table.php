<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_request_id')->constrained();
            $table->foreignId('donor_id')->constrained();
            $table->decimal('distance_km', 6, 2);
            $table->enum('status', ['notified', 'accepted', 'declined'])->default('notified');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->unique(['blood_request_id', 'donor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_matches');
    }
};
