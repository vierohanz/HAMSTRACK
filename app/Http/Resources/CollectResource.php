<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'humidity' => $this->humidity,
            'temperature' => $this->temperature,
            'rainfall' => $this->rainfall,
            'irradiance' => $this->irradiance,
            'wind_speed' => $this->wind_speed,
            'wind_direction' => $this->wind_direction,
            'atmospheric_pressure' => $this->atmospheric_pressure,
            'created_at' => $this->created_at,
        ];
    }
}
