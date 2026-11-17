<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Used when the app starts online (docker/start.sh): adds the sample and demo data only to an empty
// database, so restarting or redeploying never duplicates or wipes data.
Artisan::command('lifeline:seed-if-empty', function () {
    if (User::query()->exists()) {
        $this->info('The database already has users; sample data not added.');

        return;
    }

    $this->call('db:seed', ['--force' => true]);
})->purpose('Add the sample and demo data when the database is empty');
