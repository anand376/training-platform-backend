<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Exception;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|unique:users',
                'password' => 'required|string|confirmed|min:6',
                'role'     => 'in:admin,student'
            ]);

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'role'     => $data['role'] ?? 'student',
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user'         => $user,
                'access_token' => $token,
                'token_type'   => 'Bearer'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Registration failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Login
    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email'    => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $data['email'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials.'
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user'         => $user,
                'access_token' => $token,
                'token_type'   => 'Bearer'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Login failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Get current user
    public function me(Request $request)
    {
        try {
            \Log::info('Current user in me:', [$request->user()]);
            return response()->json($request->user());
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch user.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
