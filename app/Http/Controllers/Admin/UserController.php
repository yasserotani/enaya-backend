<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\Doctor;
use App\Models\User;
use App\Services\DoctorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(private readonly DoctorService $doctorService)
    {
    }

    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest()->paginate(15),
        ]);
    }

    public function show(User $user)
    {
        if ($user->hasRole('patient')) {
            $user->loadMissing('patient');
            return response()->json([
                'success' => true,
                'data' => new PatientResource($user->patient),
            ]);
        }

        if ($user->hasRole('doctor')) {
            $user->loadMissing('doctor.department');
            return response()->json([
                'success' => true,
                'data' => new DoctorResource($user->doctor),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => new UserResource($user),
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        // if doctor
        if ($validated['role'] === 'doctor') {

            $doctor = $this->doctorService->createDoctor($validated);

            return response()->json([
                'success' => true,
                'message' => 'Doctor created successfully.',
                'data' => new UserResource($doctor->user->load('roles', 'doctor.department')),
            ], 201);
        }

        // if Receptionist or admin
        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole($validated['role']);

            return $user;
        });

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => new UserResource($user->load('roles')),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:users,email,{$user->id}",
            'specialty' => 'sometimes|string|max:255',
            'department_id' => 'sometimes|integer|exists:departments,id',
        ]);

        $user->update(collect($validated)->only(['name', 'email'])->toArray());

        if ($user->hasRole('doctor')) {
            $user->doctor?->update(
                collect($validated)->only(['specialty', 'department_id'])->toArray()
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data' => $user->load('roles', 'doctor'),
        ]);
    }


    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        // if the user doctor or patient  , soft delete doctor row and set use is_active to false
        if ($user->hasRole('doctor')) {
            $user->loadMissing('doctor');

            if ($user->doctor) {
                $user->doctor->delete(); // soft delete
            }
            // prevent logging in by setting is_active to false
            $user->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Doctor deleted successfully.',
            ]);
        }

        if ($user->hasRole('patient')) {
            $user->loadMissing('patient');

            if ($user->patient) {
                $user->patient->delete(); // soft delete
            }

            $user->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Patient deleted successfully.',
            ]);
        }

        // admin or reception , no soft delete
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }

    public function activate(User $user)
    {
        $user->update(['is_active' => true]);
        return response()->json([
            'success' => true,
            'message' => 'User activated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function deactivate(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account.',
            ], 403);
        }
        $user->update(['is_active' => false]);
        return response()->json([
            'success' => true,
            'message' => 'User deactivated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function test()
    {
        $user = User::find(1);
        $user->patient()
            ->where('created_at', '<=', now()->subDays(10))
            ->get();

    }
}
