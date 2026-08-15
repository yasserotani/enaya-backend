<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Doctor\UpdateDoctorWorkingHoursRequest;
use App\Http\Resources\DoctorResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $doctor = $request->user()->doctor;

        if (! $doctor) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DoctorResource($doctor->load(['user', 'department'])),
        ]);
    }

    public function updateWorkingHours(UpdateDoctorWorkingHoursRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $doctor = $request->user()->doctor;

            if (! $doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor record not found',
                ], 404);
            }

            $doctor->update($request->only(['working_hours_start', 'working_hours_end']));

            return response()->json([
                'success' => true,
                'message' => 'Working hours updated successfully',
                'data' => new DoctorResource($doctor->fresh()->load(['user', 'department'])),
            ]);
        });
    }
}
