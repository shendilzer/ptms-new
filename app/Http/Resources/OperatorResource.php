<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperatorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'address' => $this->address,
            'email_address' => $this->email_address,
            'contact_number' => $this->contact_number,
            'driver_id' => $this->driver_id,
            'driver_fullname' => $this->driver->driver_fullname,
            'driver_license_number' => $this->driver->driver_license_number,
            'motorcycle_id' => $this->motorcycle_id,
            'plate_number' => $this->motorcycle->plate_number,
            'mtop_number' => $this->mtop_number,
            'toda_id' => $this->toda_id,
            'toda_name' => $this->toda->toda_name,
            'date_registered' => $this->date_registered->format('Y-m-d'),
            'date_last_paid' => $this->date_last_paid ? $this->date_last_paid->format('Y-m-d') : null,
            'permit_status' => $this->permit_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
