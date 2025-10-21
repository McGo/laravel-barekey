<?php

namespace McGo\Barekey;

use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use McGo\Barekey\Commands\MakeApiKey;
use McGo\Barekey\Enums\DefaultAbilities;
use McGo\Barekey\Models\ApiKey;
use McGo\Barekey\Observers\CreateApiKeyCalcualtedFields;

class BarekeyServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
    public function boot()
    {
        $this->bootCommands();
        $this->bootAuth();
        $this->bootGate();
        $this->bootObserver();
    }

    private function bootAuth()
    {
        Auth::extend('barekey', function ($app, $name, array $config) {
            return new RequestGuard(function ($request) {
                $bearer = $request->bearerToken() ?: $request->header('X-Barekey-Token');
                if (!$bearer) {
                    return null;
                }
                $parts = explode(':', $bearer);
                if (count($parts) !== 2) {
                    return null;
                }

                $key = ApiKey::where('uuid', $parts[0])->first();
                if (!$key) {
                    return null;
                }

                if ($key->token  !== $parts[1]) {
                    return null;
                }

                $key->updateQuietly([
                    'last_used_at' => now(),
                ]);

                return $key;
            }, $app['request']);
        });
    }

    private function bootGate()
    {
        Gate::before(function ($user, string $ability) {
            $granted = (array) data_get($user, 'abilities', []);
            $class = config('auth.guards.barekey.abilities', DefaultAbilities::class);
            return $class::granted($granted, $ability) ? true : null;
        });
    }

    private function bootObserver()
    {
        ApiKey::observe(CreateApiKeyCalcualtedFields::class);
    }

    private function bootCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeApiKey::class,
            ]);
        }
    }

    private function registerConfig()
    {
        config([
            'auth.guards.barekey' => array_merge([
                'driver' => 'sanctum',
                'provider' => null,
                'abilities' => DefaultAbilities::class,
            ], config('auth.guards.barekey', [])),
        ]);
    }
}