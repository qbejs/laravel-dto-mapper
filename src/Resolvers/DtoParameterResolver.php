<?php

namespace LaravelDtoMapper\Resolvers;

use Closure;
use Illuminate\Http\Request;
use LaravelDtoMapper\Attributes\MapQueryString;
use LaravelDtoMapper\Attributes\MapRequestPayload;
use ReflectionMethod;

class DtoParameterResolver
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $route = $request->route();
        
        if (!$route) {
            return $next($request);
        }

        $controller = $route->getController();
        $method = $route->getActionMethod();

        if (!$controller || !$method) {
            return $next($request);
        }

        try {
            $reflection = new ReflectionMethod($controller, $method);
        } catch (\ReflectionException) {
            return $next($request);
        }

        $resolver = new DtoResolver($request);

        foreach ($reflection->getParameters() as $parameter) {
            $attributes = $parameter->getAttributes();

            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();

                // Handle MapRequestPayload attribute
                if ($attributeInstance instanceof MapRequestPayload) {
                    $type = $parameter->getType();
                    
                    if ($type && !$type->isBuiltin()) {
                        $className = $type->getName();
                        
                        $dto = $resolver->resolveFromPayload(
                            $className,
                            $attributeInstance->validate,
                            $attributeInstance->stopOnFirstFailure
                        );

                        $request->attributes->set($parameter->getName(), $dto);
                    }
                }

                // Handle MapQueryString attribute
                if ($attributeInstance instanceof MapQueryString) {
                    $type = $parameter->getType();
                    
                    if ($type && !$type->isBuiltin()) {
                        $className = $type->getName();
                        
                        $dto = $resolver->resolveFromQuery(
                            $className,
                            $attributeInstance->validate,
                            $attributeInstance->stopOnFirstFailure
                        );

                        $request->attributes->set($parameter->getName(), $dto);
                    }
                }
            }
        }

        return $next($request);
    }
}
