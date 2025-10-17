<?php

namespace App\DTOs;

use LaravelDtoMapper\Contracts\MappableDTO;

/**
 * Example DTO for updating user
 */
class UpdateUserDTO implements MappableDTO
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
            'age' => 'required|integer|min:18|max:120',
            'phone' => 'nullable|string|regex:/^[0-9]{9,15}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'age.min' => 'Musisz mieć ukończone 18 lat.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'imię i nazwisko',
            'email' => 'adres email',
            'age' => 'wiek',
            'phone' => 'numer telefonu',
        ];
    }
}
