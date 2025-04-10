<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);
Route::middleware('auth.sanctum.json')->group(function () {
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/logout', [UserController::class, 'logout']);
});
