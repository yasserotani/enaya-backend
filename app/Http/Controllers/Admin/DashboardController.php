<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * @authenticated
     */
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [

                // KPI cards
                'total_patients' => Patient::count(),
                'total_doctors' => Doctor::count(),
                'total_receptionists' => User::role('receptionist')->count(),
                'appointments_today' => Appointment::whereDate('scheduled_at', today())->count(),
                'appointments_this_week' => Appointment::whereBetween('scheduled_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ])->count(),
                'pending_appointments' => Appointment::where('status', 'scheduled')->count(),
                'completed_today' => Appointment::whereDate('scheduled_at', today())
                    ->where('status', 'completed')
                    ->count(),

                // Recent activity
                'recent_patients' => Patient::latest()
                    ->take(5)
                    ->get(['id', 'full_name', 'phone', 'created_at']),
                'recent_appointments' => Appointment::with(
                    'patient:id,full_name',
                    'doctor.user:id,name'
                )
                    ->latest()
                    ->take(5)
                    ->get(),

                // Chart data
                'appointments_last_7_days' => Appointment::selectRaw('DATE(scheduled_at) as date, COUNT(*) as total')
                    ->where('scheduled_at', '>=', now()->subDays(6)->startOfDay())
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ],
        ]);
    }
}
