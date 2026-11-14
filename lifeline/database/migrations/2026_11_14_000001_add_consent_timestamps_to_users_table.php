<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Data Privacy Act (RA 10173) evidence: when the user agreed at registration step 3.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('privacy_consent_at')->nullable()->after('notifications_enabled');
            $table->timestamp('terms_accepted_at')->nullable()->after('privacy_consent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['privacy_consent_at', 'terms_accepted_at']);
        });
    }
};
