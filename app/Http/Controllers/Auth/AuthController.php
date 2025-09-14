<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Sections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:dts_users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => (new User)->encryptData($request->email,"d3p3d10@ict"),
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $encryptedEmail = (new User)->encryptData($request->email, "d3p3d10@ict");

        Log::info('AuthController: Login attempt', [
            'email' => $request->email,
            'encrypted_email_preview' => substr($encryptedEmail, 0, 20) . '...',
            'ip' => $request->ip()
        ]);

        if (!Auth::attempt(['email' => $encryptedEmail, 'password' => $request->password])) {
            Log::warning('AuthController: Login failed - invalid credentials', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        Log::info('User authenticated successfully: ', ['email' => $request->email]);

        $user = User::where('email', $encryptedEmail)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('AuthController: Token created', [
            'user_id' => $user->id,
            'user_email' => $request->email,
            'token_length' => strlen($token),
            'token_preview' => substr($token, 0, 10) . '...',
            'token_name' => 'auth_token'
        ]);

        // Set httpOnly cookie for web route authentication
        $cookie = cookie('auth_token', $token, 60 * 24 * 7, '/', null, true, true); // 7 days, httpOnly, secure

        Log::info('AuthController: Cookie created', [
            'cookie_name' => 'auth_token',
            'cookie_minutes' => 60 * 24 * 7,
            'cookie_path' => '/',
            'cookie_domain' => null,
            'cookie_secure' => true,
            'cookie_httponly' => true,
            'original_token_length' => strlen($token),
            'will_be_encrypted' => true
        ]);

        $responseData = [
            'isAuthenticated' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id'         => $user->id,
                'name'       => explode(' ', trim($user->name))[0], //$user->name,
                'email'      => $request->email,
                'position'   => $user->position,
                'section_id' => $user->section_id,
                'section_name' => Sections::select('section_description')->where('section_id', $user->section_id)->first()->section_description,
                'avatar'     => $user->avatar,   
            ],
        ];

        Log::info('AuthController: Login successful, returning response with cookie', [
            'user_id' => $user->id,
            'response_has_token' => isset($responseData['access_token']),
            'response_token_length' => strlen($responseData['access_token']),
            'section_name' => Sections::select('section_description')->where('section_id', $user->section_id)->first()->section_description,
            'cookie_will_be_attached' => true
        ]);

        return response()->json($responseData)->cookie($cookie);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        // Clear the auth_token cookie
        $cookie = cookie()->forget('auth_token');
        
        return response()->json(['message' => 'Logged out successfully'])->cookie($cookie);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function check(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'isAuthenticated' => false
            ], 401);
        }

        return response()->json([
            'isAuthenticated' => true,
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'position' => $request->user()->position,
                'section_id' => $request->user()->section_id,
                'section_name' => Sections::where('section_id', $user->section_id)->first()->name,
            ]
        ]);
    }
}
