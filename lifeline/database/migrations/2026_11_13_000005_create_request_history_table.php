<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blood_request_id')->constrained();
            $table->foreignId('changed_by')->nullable()->constrained('users');
            $table->string('old_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->string('remarks')->nullable();
            // History is append-only, so there is no updated_at.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_history');
    }
};
