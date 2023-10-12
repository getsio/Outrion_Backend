<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->group(function () {
  Route::post('/register', [UserController::class, 'register']);
  Route::post('/login', [UserController::class, 'login']);
  Route::middleware(['auth:sanctum', 'check.token'])->group(function () {
    Route::get('/user', [UserController::class, 'getAuthenticatedUser']);
    Route::get('/user-with-roles', [UserController::class, 'getAuthenticatedUserRoles']);
    Route::post('change-password', [UserController::class, 'changePassword']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::put('/user/{user}', [UserController::class, 'update']);
    Route::middleware(['check.admin'])->group(function () {
      Route::post('/role', [RoleController::class, 'store']);
      Route::get('/roles', [RoleController::class, 'index']);
      Route::get('/roles-with-users', [RoleController::class, 'indexWithUsers']);
      Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
      Route::delete('roles/{role}', [RoleController::class, 'destroy']);
      Route::delete('users/{user}/roles/{role}', [UserController::class, 'removeRole']);
      Route::delete('users/{user}', [UserController::class, 'destroy']);
      Route::get('/users', [UserController::class, 'getAllUsers']);
      Route::get('/users-with-roles', [UserController::class, 'indexWithRoles']);
      Route::get('/users-by-initial/{initial}', [UserController::class, 'initial']);
      Route::get('/role-with-user/{role}', [RoleController::class, 'getRole']);
    });
  });
});