<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $role = $data['role'] ?? 'driver';
        if (!in_array($role, ['owner', 'driver'], true)) {
            $role = 'driver';
        }

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' =>  $data['password'],
            'role' => $role,
        ]);

        $deviceName = $data['device_name'] ?? 'api';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Registered successfully.',
            'errors' => null,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $login = $data['login'];
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $field => $login,
            'password' => $data['password'],
        ];

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Invalid credentials.',
                'errors' => [
                    'login' => ['Invalid credentials.'],
                ],
            ], 401);
        }

        $user = Auth::user();
        $deviceName = $data['device_name'] ?? 'api';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Logged in successfully.',
            'errors' => null,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Logged out successfully.',
            'errors' => null,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user(),
            ],
            'message' => 'Profile fetched.',
            'errors' => null,
        ]);
    }
}
