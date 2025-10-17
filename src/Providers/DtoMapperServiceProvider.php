<?php

namespace LaravelDtoMapper\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use LaravelDtoMapper\Resolvers\DtoParameterResolver;

class DtoMapperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DtoParameterResolver::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register the parameter resolver globally
        $kernel = $this->app->make(Kernel::class);
        $kernel->prependMiddleware(DtoParameterResolver::class);
    }
}
