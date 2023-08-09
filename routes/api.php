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
    Route::get('/users', [UserController::class, 'getAllUsers']);
    Route::get('/user', [UserController::class, 'getAuthenticatedUser']);
    Route::post('change-password', [UserController::class, 'changePassword']);
    Route::post('/logout', [UserController::class, 'logout']);
    Route::post('/role', [RoleController::class, 'store']);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole']);
  });
});