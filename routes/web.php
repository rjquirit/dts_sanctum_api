<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifySanctumToken;

Route::view('/search', 'search')->name('search');
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');


Route::middleware(VerifySanctumToken::class)->group(function () {
    Route::view('/', 'incoming')->name('incoming');
    Route::get('/add', function () {return view('add');})->name('add');
    Route::get('/find', function () {return view('find');})->name('find');
    Route::get('/profile', function () {return view('profile');})->name('profile');

    //document views
    Route::get('/mydocs', fn() => view('mydocs', ['type' => 'mydocs']))->name('mydocs');
    Route::get('/incoming', fn() => view('incoming', ['type' => 'incoming']))->name('incoming');
    Route::get('/pending', fn() => view('pending', ['type' => 'pending']))->name('pending');  
    Route::get('/forward', fn() => view('forward', ['type' => 'forward']))->name('forward');
    Route::get('/archive', fn() => view('archive', ['type' => 'deferred']))->name('archive');
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