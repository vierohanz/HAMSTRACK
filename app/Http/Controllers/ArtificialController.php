<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArtificialRequest;
use App\Http\Resources\ArtificialResource;
use App\Models\artificial_intellegence;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SendMessageResources;

class ArtificialController extends Controller
{
    public function allTable()
    {
        $table = artificial_intellegence::all();
        return ArtificialResource::collection($table);
    }

    public function halfTable()
    {
        $table = artificial_intellegence::orderBy('id', 'desc')->take(70)->get();
        return ArtificialResource::collection($table);
    }

    public function postTable(ArtificialRequest $request): ArtificialResource
    {
        $validated = $request->validated();

        $artificialIntellegence = artificial_intellegence::updateOrCreate(
            ['date' => $validated['date']],
            [
                'irradiance' => $validated['irradiance'],
                'temperature_c' => $validated['temperature_c'],
                'precipitation_mm_per_hr' => $validated['precipitation_mm_per_hr'],
                'humidity_percent' => $validated['humidity_percent'],
            ]
        );

        return new ArtificialResource($artificialIntellegence);
    }
}
