<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
    /**
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        // get user by email or name
        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->usernameOrEmail)
                ->orWhere('name', $request->usernameOrEmail);
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Email or password is incorrect',
                'errorCode' => 401,
            ], 401);
        }

        // Check if the user is active
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Account has been deactivated.',
                'errorCode' => 'ACCOUNT_DISABLED',
            ], 403);
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

    /**
     * @unauthenticated
     */
    public function register(RegisterRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $patientRole = Role::findOrCreate('patient', 'web');
            $data = $request->validated();

            $user = User::create([
                'name' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($patientRole);

            // check if a walk-in record exists with matching phone or email
            // match existing receptionist-created walk-in by phone only (patients table stores phone)
            $existingPatient = Patient::whereNull('user_id')
                ->where('phone', $data['phone'])
                ->first();

            if ($existingPatient) {
                // link the existing walk-in record to the new account
                // profile_completed stays true since receptionist already filled it
                $existingPatient->update([
                    'user_id' => $user->id,
                ]);
            } else {
                // no walk-in record found — create a minimal empty record
                // patient must complete their profile through the app
                $user->patient()->create([
                    'user_id' => $user->id,
                    'full_name' => $user->name,
                    'phone' => $data['phone'],
                    'profile_completed' => false,
                    // // store phone on patient record for walk-in matching
                    // 'phone' => $data['phone'],
                    // full_name, date_of_birth, gender, address, job
                    // all left null — filled in ProfileController@complete
                ]);
            }

            $token = $user->createToken('auth_token', ['*'], now()->addDays(30));
            $expiresAt = $token->accessToken->expires_at->toISOString();
            $user->load('patient');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => new UserResource($user),
                    'profileCompleted' => $user->patient?->profile_completed,
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
        $request->validate([
            'fcm_token' => 'nullable|string',
        ]);

        // delete the notification token
        if ($request->fcm_token) {
            $request->user()->deviceTokens()->where('fcm_token', $request->fcm_token)->delete();
        }

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
