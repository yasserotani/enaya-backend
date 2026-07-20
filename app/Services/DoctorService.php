<?php

namespace App\Services;

use App\Http\Resources\DoctorResource;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class DoctorService
{
    /**
     * @throws Throwable
     */
    public function createDoctor(array $data): Doctor
    {
        return DB::transaction(function () use ($data) {
            // create user account
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole('doctor');

            // create the doctor row
            return Doctor::create([
                'user_id' => $user->id,
                'full_name' => $data['name'],
                'phone' => $data['phone'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'department_id' => $data['department_id'],
                'specialty' => $data['specialty'],
                'working_hours_start' => $data['working_hours_start'] ?? '08:00',
                'working_hours_end' => $data['working_hours_end'] ?? '14:00',
            ]);
        });
    }

    public function indexDoctors(Request $request): LengthAwarePaginator
    {
        $query = Doctor::with(['department', 'user']);

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

        return $doctors;
    }
}
