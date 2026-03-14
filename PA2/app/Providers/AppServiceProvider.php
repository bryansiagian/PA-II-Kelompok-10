<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Logika Super Admin Spatie
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // 2. Memastikan URL yang dikirim di email benar (terutama jika pakai HTTPS)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
