<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // get user by email or name
        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->usernameOrEmail)
                ->orWhere('name', $request->usernameOrEmail);
        })->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Email or password is incorrect',
                'errorCode' => 401,
            ], 401);
        }

        $user->tokens()->delete(); // delete old tokens
        $token = $user->createToken('auth_token', ['*'], now()->addDays(30));
        $expiresAt = $token->accessToken->expires_at->toISOString();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'token' => $token->plainTextToken,
                'expiresAt' => $expiresAt,
            ],
            'error' => null,
            'errorCode' => null,
        ]);
    }

    public function register(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $patientRole = Role::findOrCreate('patient', 'web');

            $user = User::create([
                'name' => $request->userName,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole($patientRole);

            Patient::create([
                'user_id' => $user->id,
                'address' => $request->address,
            ]);

            $token = $user->createToken('auth_token', ['*'], now()->addDays(30));
            $expiresAt = $token->accessToken->expires_at->toISOString();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => new UserResource($user),
                    'token' => $token->plainTextToken,
                    'expiresAt' => $expiresAt,
                ],
                'error' => null,
                'errorCode' => null,
            ], 201);
        });
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'error' => null,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($request->user()),
            ],
            'error' => null,
            'errorCode' => null,
        ]);
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30));
        $expiresAt = $token->accessToken->expires_at->toISOString();

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
            'expiresAt' => $expiresAt,
            'error' => null,
        ]);
    }
}
