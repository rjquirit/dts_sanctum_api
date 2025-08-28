<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class VerifySanctumToken
{
    /**
     * Handle an incoming request for web routes using Sanctum token verification.
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('VerifySanctumToken: Starting token verification', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // First check if user is already authenticated via session
        if (Auth::check()) {
            Log::info('VerifySanctumToken: User already authenticated via session', [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email ?? 'unknown'
            ]);
            return $next($request);
        }

        Log::info('VerifySanctumToken: No session auth, checking for token');

        // Try to get token from cookie - Laravel doesn't encrypt cookies by default
        $token = null;
        $rawCookieValue = $_COOKIE['auth_token'] ?? null;
        
        Log::info('VerifySanctumToken: Cookie token check', [
            'has_raw_cookie' => !is_null($rawCookieValue),
            'raw_cookie_length' => $rawCookieValue ? strlen($rawCookieValue) : 0,
            'all_cookies' => array_keys($request->cookies->all()),
            'raw_cookie_preview' => $rawCookieValue ? substr($rawCookieValue, 0, 20) . '...' : null
        ]);

        if ($rawCookieValue) {
            // The token is stored as plain text in the cookie, no decryption needed
            $token = $rawCookieValue;
            Log::info('VerifySanctumToken: Token retrieved from cookie', [
                'token_length' => strlen($token),
                'token_preview' => substr($token, 0, 10) . '...'
            ]);
        }

        // If no token in cookie, try Authorization header as fallback
        if (!$token) {
            $authHeader = $request->header('Authorization');
            Log::info('VerifySanctumToken: Checking Authorization header', [
                'has_auth_header' => !is_null($authHeader),
                'auth_header_preview' => $authHeader ? substr($authHeader, 0, 20) . '...' : null
            ]);
            
            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                $token = substr($authHeader, 7);
                Log::info('VerifySanctumToken: Extracted Bearer token', [
                    'token_length' => strlen($token),
                    'token_preview' => substr($token, 0, 10) . '...'
                ]);
            }
        }

        if (!$token) {
            Log::warning('VerifySanctumToken: No token found, redirecting to login', [
                'url' => $request->fullUrl(),
                'has_cookies' => count($request->cookies->all()) > 0,
                'cookies' => array_keys($request->cookies->all())
            ]);
            return $this->redirectToLogin($request);
        }

        Log::info('VerifySanctumToken: Attempting to find token in database', [
            'token_preview' => substr($token, 0, 10) . '...',
            'token_length' => strlen($token)
        ]);

        // Find the token in the database
        $accessToken = PersonalAccessToken::findToken($token);
        
        Log::info('VerifySanctumToken: Database token lookup result', [
            'token_found' => !is_null($accessToken),
            'has_tokenable' => $accessToken ? !is_null($accessToken->tokenable) : false,
            'token_id' => $accessToken->id ?? null,
            'tokenable_type' => $accessToken->tokenable_type ?? null,
            'tokenable_id' => $accessToken->tokenable_id ?? null,
            'expires_at' => $accessToken->expires_at ?? null,
            'last_used_at' => $accessToken->last_used_at ?? null
        ]);
        
        if (!$accessToken || !$accessToken->tokenable) {
            Log::warning('VerifySanctumToken: Invalid token attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'token_found' => !is_null($accessToken),
                'has_tokenable' => $accessToken ? !is_null($accessToken->tokenable) : false,
                'token_preview' => substr($token, 0, 10) . '...'
            ]);
            return $this->redirectToLogin($request);
        }

        // Check if token is expired (if you have token expiration)
        if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
            Log::warning('VerifySanctumToken: Token expired', [
                'token_id' => $accessToken->id,
                'expires_at' => $accessToken->expires_at,
                'current_time' => now(),
                'user_id' => $accessToken->tokenable_id
            ]);
            return $this->redirectToLogin($request);
        }

        Log::info('VerifySanctumToken: Token verification successful', [
            'user_id' => $accessToken->tokenable->id,
            'user_email' => $accessToken->tokenable->email ?? 'unknown',
            'token_id' => $accessToken->id,
            'token_name' => $accessToken->name
        ]);

        // Set the authenticated user for this request
        Auth::setUser($accessToken->tokenable);
        
        // Update last used timestamp
        $accessToken->forceFill(['last_used_at' => now()])->save();

        Log::info('VerifySanctumToken: User authenticated successfully, proceeding to next middleware');

        return $next($request);
    }

    /**
     * Redirect to login with intended URL
     */
    private function redirectToLogin(Request $request)
    {
        Log::info('VerifySanctumToken: Redirecting to login', [
            'expects_json' => $request->expectsJson(),
            'intended_url' => $request->url()
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'))->with('intended', $request->url());
    }
}
