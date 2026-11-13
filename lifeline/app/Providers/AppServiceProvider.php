<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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

        // Pagination links styled for Bootstrap instead of Tailwind.
        Paginator::useBootstrapFive();
    }
}
