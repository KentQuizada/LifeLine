<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Used by registration and password reset.
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        // Own counter for the registration POSTs (slows down email/mobile "already taken" enumeration).
        RateLimiter::for('registration', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        // Pagination links styled for Bootstrap instead of Tailwind.
        Paginator::useBootstrapFive();
    }
}
