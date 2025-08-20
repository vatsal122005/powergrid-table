<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        logger()->info('Registering user with email: ' . $request->email);
        $request->validate([
            "name" => "required|min:3",
            "email" => "required|email|unique:users",
            "password" => "required|min:6|alpha_num",
            "role" => "in:super_admin,admin,user,guest",
        ]);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => $request->role,
        ]);

        logger()->info('User created successfully');

        $token = $user->createToken('auth_token')->plainTextToken;

        logger()->info('Token created successfully');

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'token' => $token,
            'user' => $user,
        ], 201);
    }
    public function login(Request $request)
    {
        logger()->info('Attempting login for email: ' . $request->email);

        $request->validate([
            "email" => "required|email",
            "password" => "required|min:6|string",
        ]);

        $user = User::where('email', $request->email)->first();

        try {
            if (!$user || !Hash::check($request->password, $user->password)) {
                logger()->warning('Login failed for email: ' . $request->email . ' due to invalid credentials');
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }
        } catch (RuntimeException $e) {
            logger()->error('Login failed for email: ' . $request->email . ' due to password hash error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        logger()->info('User logged in successfully, token created for email: ' . $request->email);

        return response()->json([
            'success' => true,
            'message' => 'User logged in successfully',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        logger()->info('Attempting logout for user: ' . $request->user()->email);
        $request->user()->tokens()->delete();
        logger()->info('User logged out successfully, tokens deleted for email: ' . $request->user()->email);
        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully',
        ], 200);
    }
}
