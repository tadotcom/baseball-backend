<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate; // Uncomment if using Gates
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Example Policy mapping
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // --- Define Gates if needed ---
        // Example: Gate::define('update-game', function (User $user, Game $game) {
        //     // Logic to determine if the user can update the game
        //     return $user->isAdmin() || $user->id === $game->created_by; // Assuming created_by exists
        // });
        // --- End Gates ---
    }
}