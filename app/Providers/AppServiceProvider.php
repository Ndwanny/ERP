<?php

namespace App\Providers;

use App\Models\Role;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(UrlGenerator $url): void
    {
        if (config('app.force_https')) {
            $url->forceScheme('https');
        }

        RateLimiter::for('api', fn ($request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        // super-admin bypasses all gates
        Gate::before(function ($user, $ability) {
            if ($user->role?->name === 'super-admin') {
                return true;
            }
        });

        // Register a gate for every module permission
        foreach (Role::ALL_PERMISSIONS as $permission) {
            Gate::define($permission, fn ($user) => $user->role?->hasPermission($permission) ?? false);
        }
    }
}
