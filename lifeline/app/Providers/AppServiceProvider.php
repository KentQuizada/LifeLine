<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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

        // Per-IP ceiling on login POSTs, on top of LoginController's 5 tries per account: stops one
        // address from trying a few passwords against many accounts.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(30)->by($request->ip())
            ->response(fn () => back()
                ->withErrors(['login' => 'Too many login attempts from this network. Try again in a minute.'])
                ->onlyInput('login')));

        // Pagination links styled for Bootstrap instead of Tailwind.
        Paginator::useBootstrapFive();

        // Online (APP_ENV=production) every link and form uses https, even behind the host's proxy.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
