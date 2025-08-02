<?php

namespace App\Providers;

use App\Auth\SessionGuard;
use App\Auth\SessionUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register custom session user provider
        Auth::provider('session', function ($app, array $config) {
            return new SessionUserProvider($config['model']);
        });

        // Register custom session guard
        Auth::extend('session', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            $guard = new SessionGuard(
                $name,
                $provider,
                $app['session.store'],
                $app['request'],
                null, // timebox
                true  // rehashOnLogin
            );
            
            $app->refresh('request', $guard, 'setRequest');
            
            return $guard;
        });
    }
}