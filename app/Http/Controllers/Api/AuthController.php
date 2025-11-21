<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $request->authenticate();
            
            $user = Auth::user();
            
            // Create token with abilities based on user role
            $abilities = match($user->role) {
                'super_admin' => ['*'],
                'admin' => ['admin:*'],
                'student' => ['student:*'],
                default => ['student:*']
            };
            
            $token = $user->createToken('mobile_app', $abilities)->plainTextToken;
            
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'school_id' => $user->school_id,
                    'active' => $user->active,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'first_name' => ['nullable', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'phone' => ['nullable', 'string', 'max:20'],
                'date_of_birth' => ['nullable', 'date'],
                'address' => ['nullable', 'string', 'max:500'],
                'school_id' => ['required', 'exists:schools,id'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        // SECURITY: Create user without sensitive fields (role, email_verified_at)
        $user = User::create([
            'name' => $request->name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'school_id' => $request->school_id,
            // Don't set role via mass assignment - use assignRole() instead
            // Don't set active directly - use setActiveStatus() instead
        ]);

        // SECURITY: Use safe methods for sensitive fields
        $user->assignRole('user', null); // No authorizer for self-registration
        $user->setActiveStatus(true, null);

        event(new Registered($user));

        // Create token for new user
        $token = $user->createToken('mobile_app', ['student:*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'school_id' => $user->school_id,
                'active' => $user->active,
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth,
                'address' => $user->address,
                'role' => $user->role,
                'school_id' => $user->school_id,
                'active' => $user->active,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'school' => $user->school ? [
                    'id' => $user->school->id,
                    'name' => $user->school->name,
                    'email' => $user->school->email,
                    'phone' => $user->school->phone,
                ] : null
            ]
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->me($request);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($request->only(['name', 'first_name', 'last_name', 'phone', 'date_of_birth', 'address']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth,
                'address' => $user->address,
                'role' => $user->role,
                'school_id' => $user->school_id,
            ]
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $abilities = match($user->role) {
            'super_admin' => ['*'],
            'admin' => ['admin:*'],
            'student' => ['student:*'],
            default => ['student:*']
        };
        
        $token = $user->createToken('mobile_app', $abilities)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}