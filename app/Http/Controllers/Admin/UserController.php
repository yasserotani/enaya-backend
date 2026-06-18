<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Resources\DoctorResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\UserResource;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET /api/admin/users
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

            return new PatientResource($user->patient);
        }

        if ($user->hasRole('doctor')) {
            $user->loadMissing('doctor.department');

            return new DoctorResource($user->doctor);
        }

        return new UserResource($user);
    }

    // POST /api/admin/users
    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            // use transaction to ensure both user and doctor are created together, or neither if something fails
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole($validated['role']);

            if ($validated['role'] === 'doctor') {
                Doctor::create([
                    'user_id' => $user->id,
                    'full_name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'specialty' => $validated['specialty'],
                    'department_id' => $validated['department_id'],
                ]);
            }

            return $user;
        });

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data' => new UserResource(
                $user->load('roles', 'doctor.department')
            ),
        ], 201);
    }

    // PUT /api/admin/users/{user}
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

    // DELETE /api/admin/users/{user}
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully.',
        ]);
    }
}
