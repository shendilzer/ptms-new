<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'toda_name' => $this->toda_name,
            'toda_president' => $this->toda_president,
            'date_registered' => $this->date_registered->format('Y-m-d'),
            'toda_status' => $this->toda_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
