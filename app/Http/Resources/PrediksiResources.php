<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrediksiResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'humidity_percent' => $this->humidity,
            'temperature_c' => $this->temperature,
            'precipitation_mm_per_hr' => $this->rainfall,
            'irradiance' => $this->irradiance,
            'date' => $this->created_at->toDateTimeString(),

        ];
    }
}
