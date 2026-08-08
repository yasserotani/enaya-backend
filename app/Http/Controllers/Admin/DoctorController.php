<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDoctorRequest;
use App\Http\Requests\Admin\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\User;
use App\Services\DoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function __construct(private readonly DoctorService $doctorService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['department', 'user']);

        // if the amdin the deleted doctors
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $doctors = $query
            ->applyFilters($request->only([
                'department_id',
                'specialty',
                'search',
                'gender',
            ]))
            ->latest()
            ->paginate($request->get('per_page', 15));

        $doctors->setCollection(
            DoctorResource::collection($doctors->getCollection())->collection
        );

        return response()->json([
            'success' => true,
            'data' => $doctors,
            'error' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws \Throwable
     */
    public function store(StoreDoctorRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $doctor = $this->doctorService->createDoctor($validated);

        $doctor->load(['user', 'department']);

        return response()->json([
            'success' => true,
            'data' => new DoctorResource($doctor),
            'error' => null,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        $doctor->loadMissing(['department', 'user']);

        return response()->json([
            'success' => true,
            'data' => new DoctorResource($doctor),
            'error' => null,
        ]);
    }

    public function resetPassword(Request $request, Doctor $doctor)
    {
        $validated = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $doctor->user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'data' => null,
            'error' => null,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws \Throwable
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $doctor) {
            $userData = [];

            if (isset($validated['full_name'])) {
                $userData['name'] = $validated['full_name'];
            }
            if (isset($validated['email'])) {
                $userData['email'] = $validated['email'];
            }

            if (!empty($userData)) {
                $doctor->user->update($userData);
            }

            $doctor->update(Arr::except($validated, ['email', 'password']));
        });

        return response()->json([
            'success' => true,
            'data' => new DoctorResource($doctor->load(['department', 'user'])),
            'error' => null,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor): JsonResponse
    {
        DB::transaction(function () use ($doctor) {
            $doctor->delete(); // soft delete
            $doctor->user->update(['is_active' => false]); // Deactivate the related user
        });

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Doctor deleted successfully',
            'error' => null,
        ]);
    }

    /**
     * Restore the specified soft-deleted doctor and reactivate the related user.
     */
    public function restore($id): JsonResponse
    {
        $doctor = Doctor::onlyTrashed()->findOrFail($id);
        DB::transaction(function () use ($doctor) {
            $doctor->restore();
            $doctor->user()->update([
                'is_active' => true,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Doctor restored successfully',
            'data' => new DoctorResource(
                $doctor->fresh()->load(['user', 'department'])
            ),
            'error' => null,
        ]);
    }
}
