<?php

namespace App\Http\Controllers;

use App\DTOs\CreateUserDTO;
use App\DTOs\UserFilterDTO;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use LaravelDtoMapper\Attributes\MapQueryString;
use LaravelDtoMapper\Attributes\MapRequestPayload;

/**
 * Example controller showing how to use DTO mapper
 */
class UserController extends Controller
{
    /**
     * List users with optional filtering
     * 
     * Example: GET /api/users?search=john&minAge=25&sortBy=name&perPage=20
     */
    public function index(
        #[MapQueryString] UserFilterDTO $filters
    ): JsonResponse {
        $query = User::query();

        // Apply search filter
        if ($filters->search) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters->search}%")
                  ->orWhere('email', 'like', "%{$filters->search}%");
            });
        }

        // Apply age filters
        if ($filters->minAge !== null) {
            $query->where('age', '>=', $filters->minAge);
        }

        if ($filters->maxAge !== null) {
            $query->where('age', '<=', $filters->maxAge);
        }

        // Apply active filter
        if ($filters->active !== null) {
            $query->where('active', $filters->active);
        }

        // Apply sorting
        $sortBy = $filters->sortBy ?? 'created_at';
        $sortDirection = $filters->sortDirection ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        // Paginate results
        $perPage = $filters->perPage ?? 15;
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Create a new user
     * 
     * Example POST /api/users
     * Body:
     * {
     *   "name": "Jan Kowalski",
     *   "email": "jan@example.com",
     *   "password": "secret123",
     *   "password_confirmation": "secret123",
     *   "age": 25,
     *   "phone": "123456789",
     *   "interests": ["coding", "music"]
     * }
     * 
     * With file upload (multipart/form-data):
     * - avatar: [file]
     */
    public function store(
        #[MapRequestPayload] CreateUserDTO $dto
    ): JsonResponse {
        // Create user from DTO
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => bcrypt($dto->password),
            'age' => $dto->age,
            'phone' => $dto->phone,
            'interests' => json_encode($dto->interests),
        ]);

        // Handle avatar upload if provided
        if ($dto->avatar) {
            $path = $dto->avatar->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return response()->json([
            'message' => 'Użytkownik został utworzony pomyślnie.',
            'user' => $user,
        ], 201);
    }

    /**
     * Update existing user
     * 
     * Example: PUT /api/users/1
     */
    public function update(
        int $id,
        #[MapRequestPayload(validate: true)] UpdateUserDTO $dto
    ): JsonResponse {
        $user = User::findOrFail($id);

        $user->update([
            'name' => $dto->name,
            'email' => $dto->email,
            'age' => $dto->age,
            'phone' => $dto->phone,
        ]);

        return response()->json([
            'message' => 'Użytkownik został zaktualizowany.',
            'user' => $user,
        ]);
    }

    /**
     * Bulk create users
     * 
     * Example: POST /api/users/bulk
     * Body:
     * {
     *   "users": [
     *     {"name": "Jan", "email": "jan@example.com", "age": 25},
     *     {"name": "Anna", "email": "anna@example.com", "age": 30}
     *   ]
     * }
     */
    public function bulkStore(
        #[MapRequestPayload] BulkCreateUsersDTO $dto
    ): JsonResponse {
        $createdUsers = [];

        foreach ($dto->users as $userData) {
            $createdUsers[] = User::create($userData);
        }

        return response()->json([
            'message' => 'Utworzono ' . count($createdUsers) . ' użytkowników.',
            'users' => $createdUsers,
        ], 201);
    }

    /**
     * Example without validation (for trusted sources)
     */
    public function storeWithoutValidation(
        #[MapRequestPayload(validate: false)] CreateUserDTO $dto
    ): JsonResponse {
        // DTO is created but not validated
        // Use with caution!
        
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'age' => $dto->age,
        ]);

        return response()->json($user, 201);
    }
}
