<?php

namespace App\Http\Controllers;

use App\Http\Resources\CollectResource;
use App\Models\collect;
use Illuminate\Http\Request;

class CollectController extends Controller
{
    public function allCollect()
    {
        $collect = collect::all();
        return CollectResource::collection($collect);
    }

    public function latestCollect()
    {
        $collect = Collect::orderBy('id', 'desc')->first();
        return new CollectResource($collect);
    }
}
