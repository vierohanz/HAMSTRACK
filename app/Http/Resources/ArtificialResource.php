<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtificialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->date,
            'irradiance' => $this->irradiance,
            'temperature_c' => $this->temperature_c,
            'precipitation_mm_per_hr' => $this->precipitation_mm_per_hr,
            'humidity_percent' => $this->humidity_percent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
