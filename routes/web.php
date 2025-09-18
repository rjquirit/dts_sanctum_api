<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifySanctumToken;
use Illuminate\Support\Facades\Auth;

Route::view('/', 'incoming')->name('incoming');
Route::view('/search', 'search')->name('search');
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::get('/offline', function () { return view('offline');})->name('offline');
    
Route::middleware(VerifySanctumToken::class)->group(function () {

    Route::get('/add', function () {return view('add');})->name('add');
    Route::get('/find', function () {return view('find');})->name('find');
    Route::get('/profile', function () {return view('profile');})->name('profile');
    Route::get('/print/{track}/doc', function ($track) { return view('print', ['track' => $track]); })->name('print');

    //document views
    Route::get('/mydocs', fn() => view('mydocs', ['type' => 'mydocs']))->name('mydocs');
    Route::get('/incoming', fn() => view('incoming', ['type' => 'incoming']))->name('incoming');
    Route::get('/pending', fn() => view('pending', ['type' => 'pending']))->name('pending');  
    Route::get('/forward', fn() => view('forward', ['type' => 'forward']))->name('forward');
    Route::get('/deferred', fn() => view('deferred', ['type' => 'deferred']))->name('deferred');
    Route::get('/keep', fn() => view('keep', ['type' => 'keep']))->name('keep');
    Route::get('/release', fn() => view('release', ['type' => 'release']))->name('release');
});

Route::post('/logout', function () {
    $user = auth()->user();
    if ($user) {
        $user->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
    return response()->json(['message' => 'No authenticated user'], 401);
});