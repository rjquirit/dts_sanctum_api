<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Sections;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        // Redirect user to Google consent
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // Stateless avoids session/state mismatch for API calls
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Encrypt email same as your AuthController
            $encryptedEmail = (new User)->encryptData($googleUser->getEmail(), "d3p3d10@ict");

            // Lookup user by email or google_id
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', $encryptedEmail)
                        ->first();

            if (!$user) {
                // $user = User::create([
                //     'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'No Name',
                //     'email' => $encryptedEmail,
                //     'password' => Hash::make(Str::random(24)), // required if password NOT nullable
                //     'google_id' => $googleUser->getId(),
                //     'google_token' => $googleUser->token ?? null,
                //     'google_refresh_token' => $googleUser->refreshToken ?? null,
                //     'avatar' => $googleUser->getAvatar(),
                // ]);
                throw new \Exception("No existing user found for this Google account.");
            } else {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token ?? $user->google_token,
                    'google_refresh_token' => $googleUser->refreshToken ?? $user->google_refresh_token,
                    'avatar' => $googleUser->getAvatar() ?? $user->avatar,
                ]);
            }

            // Create Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('GoogleController: Token created', [
                'user_id' => $user->id,
                'user_email' => $googleUser->getEmail(),
                'token_length' => strlen($token),
                'token_preview' => substr($token, 0, 10) . '...',
                'token_name' => 'auth_token'
            ]);

            // Set cookie (same as AuthController)
            $cookie = cookie('auth_token', $token, 60 * 24 * 7, '/', null, true, true);

            $responseData = [
                'isAuthenticated' => true,
                'auth_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id'         => $user->id,
                    'name'       => explode(' ', trim($user->name))[0],
                    'email'      => $googleUser->getEmail(),
                    'position'   => $user->position,
                    'section_id' => $user->section_id,
                    'section_name' => Sections::select('section_description')->where('section_id', $user->section_id)->first()->section_description,
                    'avatar'     => $user->avatar,  
                ],
            ];

            Log::info('GoogleController: Google login successful, returning JSON with cookie', [
                'user_id' => $user->id,
                'section_name' => Sections::where('section_id', $user->section_id)->first()->name,
                'response_has_token' => isset($responseData['auth_token']),
                'response_token_length' => strlen($responseData['auth_token']),
                'cookie_will_be_attached' => true
            ]);

            return response()->json($responseData)->cookie($cookie);

        } catch (\Exception $e) {
            Log::error('Google login failed', ['err' => $e->getMessage()]);
            return response()->json([
                'isAuthenticated' => false,
                'message' => 'Google login failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
