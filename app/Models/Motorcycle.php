<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Motorcycle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plate_number',
        'motor_number',
        'chassis_number',
        'make',
        'year_model',
        'registered_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'registered_date' => 'date',
    ];

    /**
     * Get the operator associated with the motorcycle.
     */
    public function operator(): HasOne
    {
        return $this->hasOne(Operator::class);
    }

    /**
     * Scope a query to search motorcycles.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('plate_number', 'like', '%'.$search.'%')
                    ->orWhere('motor_number', 'like', '%'.$search.'%')
                    ->orWhere('chassis_number', 'like', '%'.$search.'%')
                    ->orWhere('make', 'like', '%'.$search.'%')
                    ->orWhere('year_model', 'like', '%'.$search.'%');
    }

    /**
     * Scope a query to filter by make.
     */
    public function scopeByMake($query, $make)
    {
        return $query->where('make', 'like', '%'.$make.'%');
    }

    /**
     * Scope a query to filter by year model.
     */
    public function scopeByYearModel($query, $yearModel)
    {
        return $query->where('year_model', $yearModel);
    }

    /**
     * Get the motorcycle age in years.
     */
    public function getAgeAttribute(): int
    {
        return now()->diffInYears($this->registered_date);
    }

    /**
     * Check if motorcycle registration is older than specified years.
     */
    public function isOlderThan($years): bool
    {
        return $this->age > $years;
    }
}
