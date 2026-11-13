<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Single source of a donor's donation dates; "last donation" = MAX(donated_on).
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained();
            $table->foreignId('blood_request_id')->nullable()->constrained();
            $table->foreignId('hospital_id')->nullable()->constrained();
            $table->date('donated_on');
            $table->boolean('is_self_reported')->default(false);
            $table->timestamps();

            $table->index(['donor_id', 'donated_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
