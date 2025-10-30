<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Driver extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'driver_fullname',
        'driver_license_number',
        'expiration_date',
        'driver_contact_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expiration_date' => 'date',
    ];

    /**
     * Get the operator associated with the driver.
     */
    public function operator(): HasOne
    {
        return $this->hasOne(Operator::class);
    }

    /**
     * Scope a query to search drivers.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('driver_fullname', 'like', '%'.$search.'%')
                    ->orWhere('driver_license_number', 'like', '%'.$search.'%')
                    ->orWhere('driver_contact_number', 'like', '%'.$search.'%');
    }

    /**
     * Scope a query to filter by license expiration.
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiration_date', '<=', now()->addDays($days))
                    ->where('expiration_date', '>=', now());
    }

    /**
     * Scope a query to filter expired licenses.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now());
    }

    /**
     * Check if driver license is expired.
     */
    public function isLicenseExpired(): bool
    {
        return $this->expiration_date->isPast();
    }

    /**
     * Check if driver license is expiring soon.
     */
    public function isLicenseExpiringSoon($days = 30): bool
    {
        return $this->expiration_date->between(now(), now()->addDays($days));
    }
}
