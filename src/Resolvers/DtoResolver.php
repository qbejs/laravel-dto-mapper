<?php

namespace LaravelDtoMapper\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use LaravelDtoMapper\Contracts\MappableDTO;
use LaravelDtoMapper\Exceptions\DtoValidationException;
use ReflectionClass;
use ReflectionProperty;
use ReflectionNamedType;
use ReflectionUnionType;

class DtoResolver
{
    public function __construct(
        protected Request $request
    ) {
    }

    /**
     * Resolve DTO from request body
     */
    public function resolveFromPayload(
        string $dtoClass,
        bool $validate = true,
        bool $stopOnFirstFailure = false
    ): object {
        $data = $this->getPayloadData();
        return $this->resolveDto($dtoClass, $data, $validate, $stopOnFirstFailure);
    }

    /**
     * Resolve DTO from query string
     */
    public function resolveFromQuery(
        string $dtoClass,
        bool $validate = true,
        bool $stopOnFirstFailure = false
    ): object {
        $data = $this->request->query->all();
        return $this->resolveDto($dtoClass, $data, $validate, $stopOnFirstFailure);
    }

    /**
     * Get payload data including files
     */
    protected function getPayloadData(): array
    {
        $data = $this->request->all();
        
        // Merge files into data array
        foreach ($this->request->allFiles() as $key => $file) {
            $data[$key] = $file;
        }

        return $data;
    }

    /**
     * Resolve DTO from data array
     */
    protected function resolveDto(
        string $dtoClass,
        array $data,
        bool $validate,
        bool $stopOnFirstFailure
    ): object {
        $reflection = new ReflectionClass($dtoClass);
        
        // Validate if DTO implements MappableDTO
        if (!$reflection->implementsInterface(MappableDTO::class)) {
            throw new \InvalidArgumentException(
                sprintf('DTO class %s must implement MappableDTO interface', $dtoClass)
            );
        }

        // Create temporary instance to get validation rules
        $tempInstance = $reflection->newInstanceWithoutConstructor();
        
        if ($validate) {
            $this->validateData($tempInstance, $data, $stopOnFirstFailure);
        }

        // Map data to DTO properties
        return $this->mapDataToDto($reflection, $data);
    }

    /**
     * Validate data using Laravel validator
     */
    protected function validateData(
        MappableDTO $dto,
        array $data,
        bool $stopOnFirstFailure
    ): void {
        $rules = $dto->rules();
        $messages = $dto->messages();
        $attributes = $dto->attributes();

        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($stopOnFirstFailure) {
            $validator->stopOnFirstFailure();
        }

        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            $firstError = array_key_first($errors);
            
            throw new DtoValidationException(
                field: $firstError ?? 'unknown',
                receivedValue: $data[$firstError] ?? null,
                expectedType: $this->getExpectedTypeFromRules($rules[$firstError] ?? ''),
                errors: $errors
            );
        }
    }

    /**
     * Map validated data to DTO object
     */
    protected function mapDataToDto(ReflectionClass $reflection, array $data): object
    {
        $instance = $reflection->newInstanceWithoutConstructor();
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            
            if (!array_key_exists($propertyName, $data)) {
                continue;
            }

            $value = $data[$propertyName];
            $type = $property->getType();

            // Handle typed properties
            if ($type instanceof ReflectionNamedType) {
                $value = $this->castValue($value, $type);
            } elseif ($type instanceof ReflectionUnionType) {
                $value = $this->castValueUnion($value, $type);
            }

            $property->setValue($instance, $value);
        }

        return $instance;
    }

    /**
     * Cast value to specific type
     */
    protected function castValue(mixed $value, ReflectionNamedType $type): mixed
    {
        $typeName = $type->getName();

        // Handle null values
        if (is_null($value) && $type->allowsNull()) {
            return null;
        }

        // Handle UploadedFile
        if ($value instanceof UploadedFile) {
            if ($typeName === UploadedFile::class || $typeName === 'object') {
                return $value;
            }
        }

        // Handle arrays
        if ($typeName === 'array' && is_array($value)) {
            return $value;
        }

        // Handle basic types
        return match ($typeName) {
            'int' => (int) $value,
            'float' => (float) $value,
            'string' => (string) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }

    /**
     * Cast value for union types (try each type)
     */
    protected function castValueUnion(mixed $value, ReflectionUnionType $type): mixed
    {
        foreach ($type->getTypes() as $subType) {
            if ($subType instanceof ReflectionNamedType) {
                try {
                    return $this->castValue($value, $subType);
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return $value;
    }

    /**
     * Extract expected type from validation rules
     */
    protected function getExpectedTypeFromRules(mixed $rules): string
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        if (!is_array($rules)) {
            return 'mixed';
        }

        $typeRules = ['string', 'integer', 'numeric', 'array', 'boolean', 'file', 'image'];
        
        foreach ($rules as $rule) {
            $ruleName = is_string($rule) ? explode(':', $rule)[0] : '';
            
            if (in_array($ruleName, $typeRules)) {
                return $ruleName;
            }
        }

        return 'mixed';
    }
}
