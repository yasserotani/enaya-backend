<?php

namespace App\Models;

use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['user_id', 'full_name', 'phone', 'date_of_birth', 'gender', 'address', 'job', 'profile_completed'])]
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory, HasRoles;

    public function scopeApplyFilters($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('full_name', 'like', "%{$filters['search']}%")
                    ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['has_account'])) {
            $filters['has_account'] === 'true'
                ? $query->whereNotNull('user_id')
                : $query->whereNull('user_id');
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
