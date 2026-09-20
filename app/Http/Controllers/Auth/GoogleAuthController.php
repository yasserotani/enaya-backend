<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class GoogleAuthController extends Controller
{
    /**
     * @unauthenticated
     */
    public function handleGoogleLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->token,
        ]);

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Invalid Google token',
                'errorCode' => 401,
            ], 401);
        }

        $googleData = $response->json();
        $googleClientId = config('services.google.client_id');

        if (($googleData['aud'] ?? null) !== $googleClientId) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Token client ID mismatch',
                'errorCode' => 401,
            ], 401);
        }

        $email = $googleData['email'] ?? null;
        $googleId = $googleData['sub'] ?? null;

        if (! $email || ! $googleId) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Google account data is incomplete',
                'errorCode' => 422,
            ], 422);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = DB::transaction(function () use ($googleData, $email, $googleId) {
                $patientRole = Role::findOrCreate('patient', 'web');

                $user = User::create([
                    'name' => $googleData['name'] ?? 'Google User',
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                    'google_id' => $googleId,
                    'email_verified_at' => now(),
                ]);

                $user->assignRole($patientRole);

                $user->patient()->create([
                    'user_id' => $user->id,
                    'full_name' => $user->name,
                    'phone' => null,
                    'profile_completed' => false,
                ]);

                return $user;
            });
        }

        if (! $user->google_id) {
            $user->update(['google_id' => $googleId]);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => 'Account has been deactivated.',
                'errorCode' => 'ACCOUNT_DISABLED',
            ], 403);
        }

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30));
        $expiresAt = $token->accessToken->expires_at->toISOString();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
                'profileCompleted' => $user->patient?->profile_completed ?? false,
                'token' => $token->plainTextToken,
                'expiresAt' => $expiresAt,
            ],
            'error' => null,
            'errorCode' => null,
        ]);
    }
}
