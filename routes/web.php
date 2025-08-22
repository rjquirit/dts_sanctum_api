
<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::view('/search', 'search')->name('search');
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::post('/logout', function () {
    $user = auth()->user();
    if ($user) {
        $user->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
    return response()->json(['message' => 'No authenticated user'], 401);
});

Route::view('/offline', 'offline');