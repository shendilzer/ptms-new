<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'driver_fullname' => $this->driver_fullname,
            'driver_license_number' => $this->driver_license_number,
            'expiration_date' => $this->expiration_date->format('Y-m-d'),
            'driver_contact_number' => $this->driver_contact_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
