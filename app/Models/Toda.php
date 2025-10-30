<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Toda extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'toda';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'toda_name',
        'toda_president',
        'date_registered',
        'toda_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_registered' => 'date',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date_registered',
    ];

    /**
     * Get the operators for the TODA.
     */
    public function operators(): HasMany
    {
        return $this->hasMany(Operator::class);
    }

    /**
     * Scope a query to search TODA.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('toda_name', 'like', '%'.$search.'%')
                    ->orWhere('toda_president', 'like', '%'.$search.'%');
    }

    /**
     * Scope a query to only include active TODA.
     */
    public function scopeActive($query)
    {
        return $query->where('toda_status', 'active');
    }

    /**
     * Scope a query to only include inactive TODA.
     */
    public function scopeInactive($query)
    {
        return $query->where('toda_status', 'inactive');
    }

    /**
     * Get the count of active operators in this TODA.
     */
    public function getActiveOperatorsCountAttribute(): int
    {
        return $this->operators()->where('permit_status', '!=', 'retire')->count();
    }

    /**
     * Get the count of operators by permit status.
     */
    public function getOperatorsCountByStatus(): array
    {
        return [
            'new' => $this->operators()->where('permit_status', 'new')->count(),
            'renew' => $this->operators()->where('permit_status', 'renew')->count(),
            'retire' => $this->operators()->where('permit_status', 'retire')->count(),
        ];
    }

    /**
     * Check if TODA is active.
     */
    public function isActive(): bool
    {
        return $this->toda_status === 'active';
    }

    /**
     * Activate the TODA.
     */
    public function activate(): void
    {
        $this->update(['toda_status' => 'active']);
    }

    /**
     * Deactivate the TODA.
     */
    public function deactivate(): void
    {
        $this->update(['toda_status' => 'inactive']);
    }
}
