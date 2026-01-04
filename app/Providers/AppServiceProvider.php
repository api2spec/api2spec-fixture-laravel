<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\MemoryStore;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register MemoryStore as singleton
        $this->app->singleton(MemoryStore::class, function () {
            return new MemoryStore();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
