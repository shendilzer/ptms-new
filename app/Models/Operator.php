<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Operator extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fullname',
        'address',
        'email_address',
        'contact_number',
        'driver_id',
        'motorcycle_id',
        'mtop_number',
        'toda_id',
        'date_registered',
        'date_last_paid',
        'permit_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_registered' => 'date',
        'date_last_paid' => 'date',
    ];

    /**
     * Get the driver associated with the operator.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the motorcycle associated with the operator.
     */
    public function motorcycle(): BelongsTo
    {
        return $this->belongsTo(Motorcycle::class);
    }

    /**
     * Get the toda associated with the operator.
     */
    public function toda(): BelongsTo
    {
        return $this->belongsTo(Toda::class);
    }

    /**
     * Scope a query to search operators.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('fullname', 'like', '%'.$search.'%')
                    ->orWhere('email_address', 'like', '%'.$search.'%')
                    ->orWhere('mtop_number', 'like', '%'.$search.'%')
                    ->orWhere('contact_number', 'like', '%'.$search.'%');
    }

    /**
     * Scope a query to filter by permit status.
     */
    public function scopeByPermitStatus($query, $status)
    {
        return $query->where('permit_status', $status);
    }

    /**
     * Scope a query to filter by TODA.
     */
    public function scopeByToda($query, $todaId)
    {
        return $query->where('toda_id', $todaId);
    }
}
