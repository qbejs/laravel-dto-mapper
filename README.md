# 📦 Laravel DTO Mapper

[![Latest Stable Version](https://poser.pugx.org/qbejs/laravel-dto-mapper/v/stable)](https://packagist.org/packages/qbejs/laravel-dto-mapper)
[![Total Downloads](https://poser.pugx.org/qbejs/laravel-dto-mapper/downloads)](https://packagist.org/packages/qbejs/laravel-dto-mapper)
[![License](https://poser.pugx.org/qbejs/laravel-dto-mapper/license)](https://packagist.org/packages/qbejs/laravel-dto-mapper)
[![PHP Version](https://img.shields.io/packagist/php-v/qbejs/laravel-dto-mapper)](https://packagist.org/packages/qbejs/laravel-dto-mapper)
[![Update Packagist](https://github.com/qbejs/laravel-dto-mapper/actions/workflows/packagist.yml/badge.svg?branch=master)](https://github.com/qbejs/laravel-dto-mapper/actions/workflows/packagist.yml)

Automatic HTTP request mapping to DTO classes in Laravel using **PHP 8 Attributes**. A simple, clean, and type-safe way to handle validation and data mapping in your controllers.

## ✨ Features

- 🎯 **PHP 8 Attributes** - Clean and modern syntax
- ✅ **Automatic Validation** - Uses Laravel's built-in Validator
- 🔒 **Type Safety** - Full support for typed properties
- 📁 **File Handling** - Automatic `UploadedFile` mapping
- 📋 **Arrays & Bulk Operations** - Complete array mapping support
- 🚀 **Zero Configuration** - Works out-of-the-box with Package Discovery
- 🧪 **Easy Testing** - DTOs are simple PHP classes

## 📋 Requirements

- PHP 8.1 or higher
- Laravel 9.x, 10.x, 11.x, or 12.x

## 🔧 Installation

```bash
composer require qbejs/laravel-dto-mapper
```

The Service Provider will be automatically registered via Laravel Package Discovery.

## 🚀 Quick Start

### 1. Create a DTO

```php
<?php

namespace App\DTOs;

use LaravelDtoMapper\Contracts\MappableDTO;

class CreateUserDTO implements MappableDTO
{
    public string $name;
    public string $email;
    public int $age;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'age' => 'required|integer|min:18',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already taken.',
            'age.min' => 'You must be at least :min years old.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'full name',
            'email' => 'email address',
            'age' => 'age',
        ];
    }
}
```

### 2. Use in Controller

```php
<?php

namespace App\Http\Controllers;

use App\DTOs\CreateUserDTO;
use LaravelDtoMapper\Attributes\MapRequestPayload;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function store(
        #[MapRequestPayload] CreateUserDTO $dto
    ): JsonResponse {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'age' => $dto->age,
        ]);

        return response()->json($user, 201);
    }
}
```

### 3. Done! 🎉

Your endpoint now:
- ✅ Automatically validates data
- ✅ Returns clear validation errors
- ✅ Maps data to type-safe DTO
- ✅ Is easy to test

## 📚 Documentation

### Available Attributes

#### `#[MapRequestPayload]` - Request Body

Maps data from request body (POST/PUT/PATCH) to DTO.

```php
public function store(
    #[MapRequestPayload] CreateUserDTO $dto
): JsonResponse {
    // $dto contains validated data from request body
}
```

**Options:**
- `validate: bool` - Enable validation (default: true)
- `stopOnFirstFailure: bool` - Stop at first validation error (default: false)

**Examples:**
```php
#[MapRequestPayload(validate: false)]
#[MapRequestPayload(stopOnFirstFailure: true)]
```

#### `#[MapQueryString]` - URL Parameters

Maps query string parameters (GET) to DTO.

```php
public function index(
    #[MapQueryString] UserFilterDTO $filters
): JsonResponse {
    // $filters contains parameters from ?search=...&page=...
}
```

### File Handling

#### Single File Upload

```php
use Illuminate\Http\UploadedFile;

class CreatePostDTO implements MappableDTO
{
    public string $title;
    public ?UploadedFile $thumbnail;

    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'thumbnail' => 'nullable|image|max:2048',
        ];
    }
    
    public function messages(): array { return []; }
    public function attributes(): array { return []; }
}
```

#### Multiple File Uploads

```php
class CreatePostDTO implements MappableDTO
{
    public string $title;
    public array $attachments; // array of UploadedFile

    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240',
        ];
    }
    
    public function messages(): array { return []; }
    public function attributes(): array { return []; }
}
```

#### Usage in Controller

```php
public function store(
    #[MapRequestPayload] CreatePostDTO $dto
): JsonResponse {
    $post = Post::create(['title' => $dto->title]);

    if ($dto->thumbnail) {
        $path = $dto->thumbnail->store('thumbnails');
        $post->update(['thumbnail' => $path]);
    }

    foreach ($dto->attachments as $file) {
        $post->attachments()->create([
            'path' => $file->store('attachments'),
        ]);
    }

    return response()->json($post, 201);
}
```

### Bulk Operations

```php
class BulkCreateUsersDTO implements MappableDTO
{
    public array $users;

    public function rules(): array
    {
        return [
            'users' => 'required|array|min:1|max:100',
            'users.*.name' => 'required|string',
            'users.*.email' => 'required|email|unique:users',
            'users.*.age' => 'required|integer|min:18',
        ];
    }
    
    public function messages(): array { return []; }
    public function attributes(): array { return []; }
}

// Usage
public function bulkStore(
    #[MapRequestPayload] BulkCreateUsersDTO $dto
): JsonResponse {
    foreach ($dto->users as $userData) {
        User::create($userData);
    }

    return response()->json([
        'created' => count($dto->users)
    ], 201);
}
```

### Error Handling

When validation fails, a 422 response is returned:

```json
{
    "message": "Validation failed for field \"email\". Expected type: email, received: string",
    "errors": {
        "email": ["This email address is already taken."],
        "age": ["You must be at least 18 years old."]
    },
    "field": "email",
    "expected_type": "email",
    "received_type": "string"
}
```

## 🧪 Testing

```php
public function test_creates_user_with_valid_data()
{
    $response = $this->postJson('/api/users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 25,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'name', 'email']);
    
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
    ]);
}

public function test_validation_fails_for_invalid_email()
{
    $response = $this->postJson('/api/users', [
        'name' => 'John',
        'email' => 'invalid-email',
        'age' => 25,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
}
```

## 💡 Best Practices

### DTO Organization

Structure your DTOs by feature/entity:

```
app/DTOs/
├── User/
│   ├── CreateUserDTO.php
│   ├── UpdateUserDTO.php
│   └── UserFilterDTO.php
├── Post/
│   ├── CreatePostDTO.php
│   └── PostSearchDTO.php
└── Common/
    ├── PaginationDTO.php
    └── SortingDTO.php
```

### Naming Conventions

- **Create**: `Create{Entity}DTO` - for creating new resources
- **Update**: `Update{Entity}DTO` - for updating existing resources
- **Filter/Search**: `{Entity}FilterDTO` - for search parameters
- **Bulk**: `Bulk{Action}{Entity}DTO` - for bulk operations

### Always Use Type Hints

```php
// ✅ Good
public string $name;
public int $age;
public ?string $phone;

// ❌ Bad
public $name;
public $age;
```

### DTOs Should NOT Have Constructors

```php
// ❌ Wrong - will cause errors
class CreateUserDTO implements MappableDTO
{
    public function __construct(
        public string $name  // DON'T DO THIS!
    ) {}
}

// ✅ Correct - only public properties
class CreateUserDTO implements MappableDTO
{
    public string $name;  // Just the property
}
```

## 🔍 Common Issues

### Property must not be accessed before initialization

**Problem:** DTO property is not being populated.

**Solution:** Make sure:
1. Property names match request parameter names (case-sensitive!)
2. You're sending the parameter in the request
3. For GET requests, use `#[MapQueryString]`
4. For POST/PUT/PATCH, use `#[MapRequestPayload]`

```php
// Request: ?deviceId=123 (lowercase 'd' in Id)
public string $deviceId;  // Must match exactly!

// NOT: ?deviceID=123
// NOT: public string $deviceID;
```

### Unresolvable dependency

**Problem:** DTO has a constructor with parameters.

**Solution:** Remove the constructor. DTOs should only have public properties.

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## 📄 Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes.

## 📜 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## 🙏 Credits

- [Jakub Skowron](https://github.com/qbejs)
- [All Contributors](../../contributors)

## 💬 Support

- 📫 [Create an issue](https://github.com/qbejs/laravel-dto-mapper/issues)
- 💬 [Discussions](https://github.com/qbejs/laravel-dto-mapper/discussions)

## ⭐ Show Your Support

If this package helped you, please consider giving it a ⭐ on [GitHub](https://github.com/qbejs/laravel-dto-mapper)!

---

Made with ❤️ for the Laravel community
