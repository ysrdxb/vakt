<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (config('app.env') !== 'local') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        if ($prefix = env('LIVEWIRE_ROUTE_PREFIX')) {
            \Livewire\Livewire::setUpdateRoute(function ($handle) use ($prefix) {
                return \Illuminate\Support\Facades\Route::post($prefix . '/livewire/update', $handle);
            });
            \Livewire\Livewire::setScriptRoute(function ($handle) use ($prefix) {
                return \Illuminate\Support\Facades\Route::get($prefix . '/livewire/livewire.js', $handle);
            });
        }
    }
}
