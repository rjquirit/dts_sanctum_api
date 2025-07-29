<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');


Route::view('/', 'dashboard')->name('home');
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::post('/logout', function () {
    auth()->user()->tokens()->delete();
    return response()->json(['message' => 'Logged out successfully']);
});

Route::view('/offline', 'offline');