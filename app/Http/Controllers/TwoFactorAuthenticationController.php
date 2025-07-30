<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Illuminate\Support\Facades\Log;

class TwoFactorAuthenticationController extends Controller
{
    public function getStatus(Request $request)
    {
        return response()->json([
            'two_factor_enabled' => $request->user() && method_exists($request->user(), 'hasEnabledTwoFactorAuthentication')
                ? $request->user()->hasEnabledTwoFactorAuthentication()
                : !is_null($request->user()->two_factor_secret),
            'two_factor_confirmed' => $request->user()->two_factor_confirmed ?? false
        ]);
    }

    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        try {
            $enable($request->user());

            return response()->json([
                'message' => 'Two factor authentication has been enabled',
                'qr_code' => method_exists($request->user(), 'twoFactorQrCodeSvg') ? $request->user()->twoFactorQrCodeSvg() : null,
                'recovery_codes' => method_exists($request->user(), 'recoveryCodes') ? $request->user()->recoveryCodes() : []
            ]);
        } catch (\Exception $e) {
            Log::error('Error enabling 2FA:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to enable two factor authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm)
    {
        try {
            $request->validate([
                'code' => 'required|string',
            ]);

            $confirm($request->user(), $request->code);
            // Optionally, set two_factor_confirmed flag if not set by Fortify
            if (property_exists($request->user(), 'two_factor_confirmed')) {
                $request->user()->two_factor_confirmed = true;
                $request->user()->save();
            }

            return response()->json([
                'message' => 'Two factor authentication has been confirmed'
            ]);
        } catch (\Exception $e) {
            Log::error('Error confirming 2FA:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to confirm two factor authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        try {
            $disable($request->user());

            return response()->json([
                'message' => 'Two factor authentication has been disabled'
            ]);
        } catch (\Exception $e) {
            Log::error('Error disabling 2FA:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to disable two factor authentication',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRecoveryCodes(Request $request, GenerateNewRecoveryCodes $generate)
    {
        try {
            $generate($request->user());

            return response()->json([
                'recovery_codes' => $request->user()->recoveryCodes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating recovery codes:', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to generate recovery codes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function challenge(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
            ]);

            $user = $request->user();
            if (!$user || !method_exists($user, 'validateTwoFactorAuthenticationCode') || !$user->validateTwoFactorAuthenticationCode($request->code)) {
                return response()->json([
                    'message' => 'Invalid authentication code'
                ], 422);
            }
            // Optionally, set two_factor_confirmed flag if not set by Fortify
            if (property_exists($user, 'two_factor_confirmed')) {
                $user->two_factor_confirmed = true;
                $user->save();
            }
            return response()->json([
                'message' => 'Two factor authentication successful'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in 2FA challenge:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Failed to process authentication code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function challengeRecoveryCode(Request $request)
    {
        try {
            $request->validate([
                'recovery_code' => 'required|string',
            ]);

            $user = $request->user();
            if (!$user || !method_exists($user, 'recoveryCodes') || !collect($user->recoveryCodes())->contains($request->recovery_code)) {
                return response()->json([
                    'message' => 'Invalid recovery code'
                ], 422);
            }
            if (method_exists($user, 'replaceRecoveryCode')) {
                $user->replaceRecoveryCode($request->recovery_code);
            }
            return response()->json([
                'message' => 'Recovery successful',
                'remaining_codes' => method_exists($user, 'recoveryCodes') ? count($user->recoveryCodes()) : 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error in 2FA recovery challenge:', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'message' => 'Failed to process recovery code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // --- Resourceful methods for 2FA management (optional RESTful endpoints) ---
    /**
     * Display a listing of the resource (2FA status).
     */
    public function index(Request $request)
    {
        return $this->getStatus($request);
    }

    /**
     * Store a newly created resource in storage (enable 2FA).
     */
    public function store(Request $request, EnableTwoFactorAuthentication $enable)
    {
        return $this->enable($request, $enable);
    }

    /**
     * Show the specified resource (2FA QR and recovery codes).
     */
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'qr_code' => method_exists($user, 'twoFactorQrCodeSvg') ? $user->twoFactorQrCodeSvg() : null,
            'recovery_codes' => method_exists($user, 'recoveryCodes') ? $user->recoveryCodes() : []
        ]);
    }

    /**
     * Remove the specified resource from storage (disable 2FA).
     */
    public function destroy(Request $request, DisableTwoFactorAuthentication $disable)
    {
        return $this->disable($request, $disable);
    }

}
