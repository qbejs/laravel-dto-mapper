<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;
use LaravelDtoMapper\Contracts\MappableDTO;

/**
 * Example DTO for creating a new user
 */
class CreateUserDTO implements MappableDTO
{
    public string $name;
    public string $email;
    public string $password;
    public int $age;
    public ?string $phone;
    public ?UploadedFile $avatar;
    public array $interests;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'age' => 'required|integer|min:18|max:120',
            'phone' => 'nullable|string|regex:/^[0-9]{9,15}$/',
            'avatar' => 'nullable|file|image|max:2048|mimes:jpg,jpeg,png',
            'interests' => 'required|array|min:1',
            'interests.*' => 'string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ten adres email jest już używany w systemie.',
            'age.min' => 'Musisz mieć ukończone 18 lat, aby się zarejestrować.',
            'password.min' => 'Hasło musi mieć minimum 8 znaków.',
            'password.confirmed' => 'Potwierdzenie hasła nie pasuje.',
            'avatar.max' => 'Avatar nie może być większy niż 2MB.',
            'interests.min' => 'Musisz wybrać przynajmniej jedno zainteresowanie.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'imię i nazwisko',
            'email' => 'adres email',
            'password' => 'hasło',
            'age' => 'wiek',
            'phone' => 'numer telefonu',
            'avatar' => 'zdjęcie profilowe',
            'interests' => 'zainteresowania',
        ];
    }
}
