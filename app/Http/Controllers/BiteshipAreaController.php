<?php

namespace App\Http\Controllers;

use App\Services\Biteship\BiteshipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BiteshipAreaController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:3'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $areas = BiteshipService::make()->searchAreas(
            $validated['q'],
            $validated['limit'] ?? 10,
        );

        return response()->json([
            'data' => $areas,
        ]);
    }
}
