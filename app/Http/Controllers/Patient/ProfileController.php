<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\CompletePatientProfileRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $patient = $user->patient;

        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                // from users table
                'name' => $user->name,
                'email' => $user->email,
                // phone is stored on patients table
                'phone' => $patient->phone,
                // from patients table
                'full_name' => $patient->full_name,
                'date_of_birth' => $patient->date_of_birth,
                'gender' => $patient->gender,
                'address' => $patient->address,
                'job' => $patient->job,
                'profile_completed' => $patient->profile_completed,
            ],
        ]);
    }

    public function complete(CompletePatientProfileRequest $request)
    {
        $validated = $request->validated();
        $patient = $request->user()->patient;

        // check if there is patient connected to this user
        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient record not found',
            ], 404);
        }

        // check if the profile already completed
        if ($patient->profile_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Profile already completed',
            ], 403);
        }

        $patient->update([
            'full_name' => $validated['full_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'address' => $validated['address'] ?? null,
            'job' => $validated['job'] ?? null,
            'profile_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile completed successfully',
            'data' => $patient->fresh(),
        ]);
    }

    public function update(UpdatePatientRequest $request)
    {
        $user = $request->user();
        $patient = $user->patient;

        if (! $patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient record not found',
            ], 404);
        }

        // users table fields (name & email only — phone is stored on patients)
        $user->update($request->only(['name', 'email']));

        // patients table fields (including phone)
        $patient->update($request->only([
            'phone',
            'date_of_birth',
            'gender',
            'address',
            'job',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'name' => $user->fresh()->name,
                'email' => $user->fresh()->email,
                'phone' => $patient->fresh()->phone,
                'date_of_birth' => $patient->fresh()->date_of_birth,
                'gender' => $patient->fresh()->gender,
                'address' => $patient->fresh()->address,
                'job' => $patient->fresh()->job,
            ],
        ]);
    }
}
