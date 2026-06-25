<?php

namespace App\Http\Controllers\Display;

use App\Http\Controllers\Controller;
use App\Http\Requests\Display\StoreAdEventRequest;
use App\Models\DisplayDevice;
use App\Services\StandaloneAdvertisingService;
use Illuminate\Http\JsonResponse;

class DisplayAdvertisingController extends Controller
{
    public function schedule(DisplayDevice $displayDevice, StandaloneAdvertisingService $service): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $service->displaySchedule($displayDevice),
        ]);
    }

    public function event(StoreAdEventRequest $request, DisplayDevice $displayDevice, StandaloneAdvertisingService $service): JsonResponse
    {
        $event = $service->recordDisplayEvent($displayDevice, $request->validated());

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $event->id,
                'eventType' => $event->event_type,
                'occurredAt' => $event->occurred_at->toIso8601String(),
            ],
        ], 201);
    }
}
