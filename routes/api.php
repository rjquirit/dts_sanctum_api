<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TwoFactorAuthenticationController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // User management routes
    Route::apiResource('users', UserController::class);

        // Two-Factor Authentication routes
    Route::get('/two-factor', [TwoFactorAuthenticationController::class, 'index']); // status
    Route::post('/two-factor', [TwoFactorAuthenticationController::class, 'store']); // enable
    Route::post('/two-factor/confirm', [TwoFactorAuthenticationController::class, 'confirm']); // confirm code
    Route::delete('/two-factor', [TwoFactorAuthenticationController::class, 'destroy']); // disable
    Route::get('/two-factor/recovery-codes', [TwoFactorAuthenticationController::class, 'getRecoveryCodes']); // new codes
    Route::post('/two-factor/challenge', [TwoFactorAuthenticationController::class, 'challenge']); // challenge code
    Route::post('/two-factor/challenge-recovery', [TwoFactorAuthenticationController::class, 'challengeRecoveryCode']); // challenge recovery code

});
