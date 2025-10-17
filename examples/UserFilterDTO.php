<?php

namespace App\DTOs;

use LaravelDtoMapper\Contracts\MappableDTO;

/**
 * Example DTO for filtering users in GET requests
 */
class UserFilterDTO implements MappableDTO
{
    public ?string $search;
    public ?int $minAge;
    public ?int $maxAge;
    public ?string $sortBy;
    public ?string $sortDirection;
    public ?int $perPage;
    public ?bool $active;

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'minAge' => 'nullable|integer|min:0|max:120',
            'maxAge' => 'nullable|integer|min:0|max:120|gte:minAge',
            'sortBy' => 'nullable|string|in:name,email,age,created_at',
            'sortDirection' => 'nullable|string|in:asc,desc',
            'perPage' => 'nullable|integer|min:1|max:100',
            'active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'maxAge.gte' => 'Maksymalny wiek musi być większy lub równy minimalnemu wiekowi.',
            'sortBy.in' => 'Można sortować tylko po: name, email, age, created_at.',
            'perPage.max' => 'Maksymalna liczba wyników na stronę to 100.',
        ];
    }

    public function attributes(): array
    {
        return [
            'search' => 'wyszukiwanie',
            'minAge' => 'minimalny wiek',
            'maxAge' => 'maksymalny wiek',
            'sortBy' => 'sortowanie po',
            'sortDirection' => 'kierunek sortowania',
            'perPage' => 'wyników na stronę',
            'active' => 'aktywny',
        ];
    }
}
