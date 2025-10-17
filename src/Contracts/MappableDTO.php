<?php

namespace LaravelDtoMapper\Contracts;

interface MappableDTO
{
    /**
     * Get validation rules for the DTO
     *
     * @return array<string, mixed>
     */
    public function rules(): array;

    /**
     * Get custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array;

    /**
     * Get custom attribute names for validation
     *
     * @return array<string, string>
     */
    public function attributes(): array;
}
