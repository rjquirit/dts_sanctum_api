<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\DoctypesController;
use App\Http\Controllers\OfficeController; 
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DocmainController;

use App\Http\Controllers\TwoFactorAuthenticationController;
use App\Http\Controllers\DocumentTrackingController;
use App\Http\Controllers\DocroutesController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/login/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('/docmain/track/{doc_id}', [DocumentTrackingController::class, 'track']);
Route::get('/docroutes/{doc_id}', [DocroutesController::class, 'routesForDoc']);

Route::prefix('test')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('test.users');
    Route::get('/sections', [SectionController::class, 'index'])->name('test.section');
    Route::get('/doctypes', [DoctypesController::class, 'index'])->name('test.doctypes');
    Route::get('/offices', [OfficeController::class, 'index'])->name('test.offices');
    Route::get('/auth/check', [AuthController::class, 'check']);

    Route::get('/documents-stats', [DocmainController::class, 'stats']);
    Route::get('/alldocs', [DocmainController::class, 'index'])->name('test.alldocs');
    Route::get('/documents', [DocmainController::class, 'index'])->name('test.documents');

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/check', [AuthController::class, 'check']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    
    Route::apiResource('users', UserController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('doctypes', DoctypesController::class);
    Route::apiResource('offices', OfficeController::class);
    Route::apiResource('documents', DocmainController::class);

    Route::post('documents/{id}/deactivate', [DocmainController::class, 'deactivate']);
    Route::post('documents/{id}/activate', [DocmainController::class, 'activate']);
    Route::post('documents/{id}/mark-done', [DocmainController::class, 'markDone']);
    Route::post('documents/{id}/accept', [DocmainController::class, 'accept']);
    Route::get('documents-stats', [DocmainController::class, 'stats']);

    Route::get('/two-factor', [TwoFactorAuthenticationController::class, 'index']); // status
    Route::post('/two-factor', [TwoFactorAuthenticationController::class, 'store']); // enable
    Route::post('/two-factor/confirm', [TwoFactorAuthenticationController::class, 'confirm']); // confirm code
    Route::delete('/two-factor', [TwoFactorAuthenticationController::class, 'destroy']); // disable
    Route::get('/two-factor/recovery-codes', [TwoFactorAuthenticationController::class, 'getRecoveryCodes']); // new codes
    Route::post('/two-factor/challenge', [TwoFactorAuthenticationController::class, 'challenge']); // challenge code
    Route::post('/two-factor/challenge-recovery', [TwoFactorAuthenticationController::class, 'challengeRecoveryCode']); // challenge recovery code

});
