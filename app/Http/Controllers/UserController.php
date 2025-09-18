<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;


class UserController extends Controller
{
    public function index()
    {
        $users = User::all()->map(function ($user) {
            try {
                // Decrypt email
                $encryptedEmail = $user->email;
                $decryptedEmail = (new User)->decryptData($encryptedEmail, "d3p3d10@ict");
                
                // Decrypt name with error handling
                $name = null;
                try {
                    $name = Crypt::decryptString($user->name);
                } catch (\Exception $e) {
                    Log::error('Name decryption failed: ' . $e->getMessage());
                    $name = $user->name; // Fallback to encrypted value
                }
                
                // Decrypt position with error handling
                $position = null;
                try {
                    $position = Crypt::decryptString($user->position);
                } catch (\Exception $e) {
                    Log::error('Position decryption failed: ' . $e->getMessage());
                    $position = $user->position; // Fallback to encrypted value
                }
                
                return [
                    'id' => $user->id,
                    'name' => $name,
                    'email' => $decryptedEmail,
                    'position' => $position,
                    'division_code' => $user->division_code,
                    'section_id' => $user->section_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ];
            } catch (\Exception $e) {
                Log::error('User data decryption failed: ' . $e->getMessage());
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => null,
                    'position' => $user->position,
                    'division_code' => $user->division_code,
                    'section_id' => $user->section_id,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ];
            }
        });
        
        return response()->json($users);

    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
            try {
            // Decrypt email
            $encryptedEmail = $user->email;
            $decryptedEmail = (new User)->decryptData($encryptedEmail, "d3p3d10@ict");
            
            // Decrypt name with error handling
            $name = null;
            try {
                $name = Crypt::decryptString($user->name);
            } catch (\Exception $e) {
                Log::error('Name decryption failed: ' . $e->getMessage());
                $name = $user->name; // Fallback to encrypted value
            }
            
            // Decrypt position with error handling
            $position = null;
            try {
                $position = Crypt::decryptString($user->position);
            } catch (\Exception $e) {
                Log::error('Position decryption failed: ' . $e->getMessage());
                $position = $user->position; // Fallback to encrypted value
            }
            
            return response()->json([
                'id' => $user->id,
                'name' => $name,
                'email' => $decryptedEmail,
                'position' => $position,
                'division_code' => $user->division_code,
                'section_id' => $user->section_id,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]);
        } catch (\Exception $e) {
            Log::error('User data decryption failed: ' . $e->getMessage());
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => null,
                'position' => $user->position,
                'division_code' => $user->division_code,
                'section_id' => $user->section_id,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at
            ]);
        }
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'string|min:8|nullable',
        ]);

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204);
    }

    public function sectionUsers($sectionId) : JsonResponse
    {
        $users = User::where('section_id', $sectionId)->get()->map(function ($user) {
            try {
                // Decrypt email
                // $encryptedEmail = $user->email;
                // $decryptedEmail = (new User)->decryptData($encryptedEmail, "d3p3d10@ict");
                
                // Decrypt name with error handling
                $name = null;
                try {
                    $name = Crypt::decryptString($user->name);
                } catch (\Exception $e) {
                    Log::error('Name decryption failed: ' . $e->getMessage());
                    $name = $user->name; // Fallback to encrypted value
                }
                
                // Decrypt position with error handling
                // $position = null;
                // try {
                //     $position = Crypt::decryptString($user->position);
                // } catch (\Exception $e) {
                //     Log::error('Position decryption failed: ' . $e->getMessage());
                //     $position = $user->position; // Fallback to encrypted value
                // }
                
                return [
                    'id' => $user->id,
                    'name' => $name
                    // 'email' => $decryptedEmail,
                    // 'position' => $position,
                    // 'division_code' => $user->division_code,
                    // 'section_id' => $user->section_id,
                    // 'created_at' => $user->created_at,
                    // 'updated_at' => $user->updated_at
                ];
            } catch (\Exception $e) {
                Log::error('User data decryption failed: ' . $e->getMessage());
                
                return [
                    'id' => $user->id,
                    'name' => $user->name
                    // 'email' => null,
                    // 'position' => $user->position,
                    // 'division_code' => $user->division_code,
                    // 'section_id' => $user->section_id,
                    // 'created_at' => $user->created_at,
                    // 'updated_at' => $user->updated_at
                ];
            }
        });
        
        return response()->json($users);
    }
}
