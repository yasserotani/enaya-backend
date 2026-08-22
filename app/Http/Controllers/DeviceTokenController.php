<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceTokenRequest;
use Illuminate\Http\JsonResponse;

class DeviceTokenController extends Controller
{
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $validated = $request->safe()->only(['fcm_token', 'device_type']);

        $userId = $request->user()->id;
        $now = now();

        // Use an upsert to avoid race conditions that can cause unique constraint violations.
        // Upsert will insert the token if missing, or update the existing row atomically.
        \App\Models\DeviceToken::upsert([
            [
                'fcm_token' => $validated['fcm_token'],
                'user_id' => $userId,
                'device_type' => $validated['device_type'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ], ['fcm_token'], ['user_id', 'device_type', 'updated_at']);

        return response()->json([
            'success' => true,
            'data' => null,
            'error' => null,
            'errorCode' => null,
        ]);
    }
}
