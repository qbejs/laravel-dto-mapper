<?php

namespace LaravelDtoMapper\Tests;

use LaravelDtoMapper\Contracts\MappableDTO;

class TestDTO implements MappableDTO
{
    public string $name;
    public string $email;
    public int $age;
    public ?string $phone;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'age' => 'required|integer|min:18',
            'phone' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'age.min' => 'Must be at least 18 years old.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
        ];
    }
}
