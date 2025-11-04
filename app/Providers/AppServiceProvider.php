<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // For defaultStringLength

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repositories to interfaces if using interfaces (optional but good practice)
        // Example:
        // $this->app->bind(
        //     \App\Repositories\Contracts\GameRepositoryInterface::class,
        //     \App\Repositories\GameRepository::class
        // );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix for older MySQL versions with utf8mb4 keys
        Schema::defaultStringLength(191);

        // Optional: Register model observers if needed
        // \App\Models\Game::observe(\App\Observers\GameObserver::class);

        // Optional: Load API resources globally if desired
        // \Illuminate\Http\Resources\Json\JsonResource::withoutWrapping();
    }
}