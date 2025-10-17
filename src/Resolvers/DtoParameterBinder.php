<?php

namespace LaravelDtoMapper\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use LaravelDtoMapper\Attributes\MapQueryString;
use LaravelDtoMapper\Attributes\MapRequestPayload;
use LaravelDtoMapper\Contracts\MappableDTO;
use ReflectionClass;
use ReflectionMethod;

class DtoParameterBinder
{
    public function bindDtos(Route $route, Request $request): void
    {
        $action = $route->getAction();

        if (!isset($action['controller'])) {
            return;
        }

        [$controllerClass, $method] = $this->parseController($action['controller']);

        if (!$controllerClass || !method_exists($controllerClass, $method)) {
            return;
        }

        try {
            $reflection = new ReflectionMethod($controllerClass, $method);
            $resolver = new DtoResolver($request);

            foreach ($reflection->getParameters() as $parameter) {
                $type = $parameter->getType();

                if (!$type || $type->isBuiltin()) {
                    continue;
                }

                $className = $type->getName();

                if (!class_exists($className)) {
                    continue;
                }

                $classReflection = new ReflectionClass($className);

                if (!$classReflection->implementsInterface(MappableDTO::class)) {
                    continue;
                }

                // Check for attributes
                $attributes = $parameter->getAttributes();
                $dto = null;

                foreach ($attributes as $attribute) {
                    $attributeInstance = $attribute->newInstance();

                    if ($attributeInstance instanceof MapRequestPayload) {
                        $dto = $resolver->resolveFromPayload(
                            $className,
                            $attributeInstance->validate,
                            $attributeInstance->stopOnFirstFailure
                        );
                        break;
                    }

                    if ($attributeInstance instanceof MapQueryString) {
                        $dto = $resolver->resolveFromQuery(
                            $className,
                            $attributeInstance->validate,
                            $attributeInstance->stopOnFirstFailure
                        );
                        break;
                    }
                }

                // If no attribute but implements MappableDTO, resolve from payload by default
                if (!$dto) {
                    $dto = $resolver->resolveFromPayload($className, true, false);
                }

                // Bind the DTO instance to the container
                app()->instance($className, $dto);
            }
        } catch (\Throwable $e) {
            // Let Laravel handle resolution errors naturally
        }
    }

    protected function parseController(string $controller): array
    {
        if (str_contains($controller, '@')) {
            return explode('@', $controller, 2);
        }

        return [null, null];
    }

    public function resolve($value, Route $route)
    {
        return $value;
    }
}
