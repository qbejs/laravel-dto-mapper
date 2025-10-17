<?php

// routes/api.php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Examples for Laravel DTO Mapper
|--------------------------------------------------------------------------
*/

// User Management Routes
Route::prefix('users')->group(function () {
    
    // GET /api/users - List users with filters
    // Query params: ?search=john&minAge=25&maxAge=50&sortBy=name&perPage=20
    Route::get('/', [UserController::class, 'index']);
    
    // POST /api/users - Create new user
    // Body: {"name": "Jan", "email": "jan@example.com", "age": 25, "interests": [...]}
    Route::post('/', [UserController::class, 'store']);
    
    // PUT /api/users/{id} - Update existing user
    Route::put('/{id}', [UserController::class, 'update']);
    
    // POST /api/users/bulk - Bulk create users
    // Body: {"users": [{"name": "...", "email": "...", "age": ...}, ...]}
    Route::post('/bulk', [UserController::class, 'bulkStore']);
    
    // DELETE /api/users/{id} - Delete user
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

// Blog Posts Routes
Route::prefix('posts')->group(function () {
    
    // GET /api/posts - List posts
    Route::get('/', [PostController::class, 'index']);
    
    // POST /api/posts - Create post with file upload
    // Content-Type: multipart/form-data
    // Fields: title, content, category, tags[], featured_image (file), attachments[] (files)
    Route::post('/', [PostController::class, 'store']);
    
    // PUT /api/posts/{id} - Update post
    Route::put('/{id}', [PostController::class, 'update']);
});

// Protected routes example (with Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::prefix('profile')->group(function () {
        // GET /api/profile - Get current user profile
        Route::get('/', [ProfileController::class, 'show']);
        
        // PUT /api/profile - Update profile
        Route::put('/', [ProfileController::class, 'update']);
        
        // POST /api/profile/avatar - Upload avatar
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
    });
    
});
