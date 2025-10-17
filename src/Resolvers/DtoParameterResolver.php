<?php

namespace LaravelDtoMapper\Resolvers;

use Closure;
use Illuminate\Http\Request;

/**
 * @deprecated This middleware approach is no longer used
 * DTOs are now bound via Route::matched event in DtoMapperServiceProvider
 */
class DtoParameterResolver
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // This is kept for backwards compatibility but does nothing
        // Actual DTO binding happens in DtoParameterBinder via Route::matched event
        return $next($request);
    }
}
