<?php

namespace Daglab\RedisJWT;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Daglab\RedisJWT\Cache\RedisCache;
use Daglab\RedisJWT\Contracts\RedisCacheContract;
use Daglab\RedisJWT\Guards\RedisJWTGuard;
use Daglab\RedisJWT\Providers\RedisJWTUserProvider;

class RedisJWTServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->bindRedisCache();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->overrideJWTGuard();
        $this->overrideUserProvider();
        $this->bindObservers();
    }

    protected function publishConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/jwt-redis.php', 'jwt-redis');

        $this->publishes([__DIR__ . '/config/jwt-redis.php' => config_path('jwt-redis.php')], 'config');
    }

    protected function overrideJWTGuard()
    {
        // Override JWT Guard for without DB query..
        Auth::extend('redis_jwt', function ($app, $name, array $config) {

            // Return an instance of Illuminate\Contracts\Auth\Guard...
            return new RedisJWTGuard($app['tymon.jwt'], Auth::createUserProvider($config['provider']), $app['request'], $app['events']);
        });
    }

    protected function overrideUserProvider()
    {
        /**
         * Override Eloquent Provider for fetching user with role&permission query.
         */
        Auth::provider('redis_jwt_user', function ($app, array $config) {

            // Return an instance of Illuminate\Contracts\Auth\UserProviderContract...
            return new RedisJWTUserProvider($app['hash'], $config['model']);
        });
    }

    protected function bindRedisCache()
    {
        $this->app->bind(RedisCacheContract::class, function ($app) {
            return new RedisCache();
        });
    }

    protected function bindObservers()
    {
        if (class_exists(config('jwt-redis.user_model'))) {
            config('jwt-redis.user_model')::observe(config('jwt-redis.observer'));
        }
    }
}
