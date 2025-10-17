<?php

namespace LaravelDtoMapper\Providers;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use LaravelDtoMapper\Resolvers\DtoParameterBinder;

class DtoMapperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DtoParameterBinder::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the parameter binder
        Route::bind('dto', function ($value, $route) {
            return $this->app->make(DtoParameterBinder::class)->resolve($value, $route);
        });

        // Hook into route matching to bind DTOs
        $this->app['router']->matched(function ($event) {
            $binder = $this->app->make(DtoParameterBinder::class);
            $binder->bindDtos($event->route, $event->request);
        });
    }
}
