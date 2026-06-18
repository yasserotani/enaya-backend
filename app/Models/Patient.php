<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'full_name', 'phone', 'date_of_birth', 'gender', 'address', 'job', 'profile_completed', 'emergency_contact'])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    public function scopeApplyFilters($query, array $filters)
    {

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $digitsOnly = preg_replace('/\D/', '', $search);

            $query->where(function ($q) use ($search, $digitsOnly) {
                $q->where('full_name', 'like', "%{$search}%");

                if ($digitsOnly !== '') {
                    // matches regardless of spaces, dashes, +, country code formatting
                    $q->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(phone, '-', ''), ' ', ''), '+', '') LIKE ?",
                        ["%{$digitsOnly}%"]
                    );
                } else {
                    $q->orWhere('phone', 'like', "%{$search}%");
                }
            });
        }


        if (!empty($filters['has_account'])) {
            $filters['has_account'] === 'true'
                ? $query->whereNotNull('user_id')
                : $query->whereNull('user_id');
        }

        // allow filtering by whether the profile is completed
        if (isset($filters['profile_completed']) && $filters['profile_completed'] !== '') {
            $filters['profile_completed'] === 'true'
                ? $query->where('profile_completed', true)
                : $query->where('profile_completed', false);
        }

        // created_at range filters
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // date_of_birth range filters
        if (!empty($filters['birth_from'])) {
            $query->whereDate('date_of_birth', '>=', $filters['birth_from']);
        }

        if (!empty($filters['birth_to'])) {
            $query->whereDate('date_of_birth', '<=', $filters['birth_to']);
        }

        return $query;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'date_of_birth' => 'date',
            'profile_completed' => 'boolean',
        ];
    }
}
