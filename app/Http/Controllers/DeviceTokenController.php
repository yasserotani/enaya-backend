<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviceTokenRequest;
use Illuminate\Http\JsonResponse;

class DeviceTokenController extends Controller
{
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $validated = $request->safe()->only(['fcm_token', 'device_type']);

        $request->user()->deviceTokens()->updateOrCreate(
            ['fcm_token' => $validated['fcm_token']],
            ['device_type' => $validated['device_type'] ?? null]
        );

        return response()->json([
            'success' => true,
            'data' => null,
            'error' => null,
            'errorCode' => null,
        ]);
    }
}
