<?php

namespace LaravelDtoMapper\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DtoValidationException extends Exception
{
    protected array $errors;
    protected string $field;
    protected mixed $receivedValue;
    protected string $expectedType;

    public function __construct(
        string $field,
        mixed $receivedValue,
        string $expectedType,
        array $errors = []
    ) {
        $this->field = $field;
        $this->receivedValue = $receivedValue;
        $this->expectedType = $expectedType;
        $this->errors = $errors;

        $message = sprintf(
            'Validation failed for field "%s". Expected type: %s, received: %s',
            $field,
            $expectedType,
            $this->getReceivedType($receivedValue)
        );

        parent::__construct($message);
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getReceivedValue(): mixed
    {
        return $this->receivedValue;
    }

    public function getExpectedType(): string
    {
        return $this->expectedType;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function getReceivedType(mixed $value): string
    {
        if (is_null($value)) {
            return 'null';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => $this->errors,
            'field' => $this->field,
            'expected_type' => $this->expectedType,
            'received_type' => $this->getReceivedType($this->receivedValue),
        ], 422);
    }
}
