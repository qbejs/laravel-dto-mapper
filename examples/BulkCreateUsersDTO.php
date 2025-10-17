<?php

namespace App\DTOs;

use LaravelDtoMapper\Contracts\MappableDTO;

/**
 * Example DTO for bulk user creation
 */
class BulkCreateUsersDTO implements MappableDTO
{
    public array $users;

    public function rules(): array
    {
        return [
            'users' => 'required|array|min:1|max:100',
            'users.*.name' => 'required|string|max:255',
            'users.*.email' => 'required|email|unique:users,email',
            'users.*.age' => 'required|integer|min:18|max:120',
            'users.*.phone' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'users.min' => 'Musisz podać przynajmniej jednego użytkownika.',
            'users.max' => 'Możesz utworzyć maksymalnie 100 użytkowników naraz.',
            'users.*.email.unique' => 'Adres email :input jest już zajęty.',
        ];
    }

    public function attributes(): array
    {
        return [
            'users' => 'użytkownicy',
            'users.*.name' => 'imię',
            'users.*.email' => 'email',
            'users.*.age' => 'wiek',
        ];
    }
}
