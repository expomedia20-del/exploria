<?php

namespace App\Http\Controllers;

use App\Http\Requests\Offers\StoreGameOfferEventRequest;
use App\Services\SmartOffersService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class SmartOffersController extends Controller
{
    public function page(SmartOffersService $service): Response
    {
        return Inertia::render('offers/index', $service->publicOverview());
    }

    public function index(SmartOffersService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->publicOverview()]);
    }

    public function storeGameEvent(StoreGameOfferEventRequest $request, SmartOffersService $service): JsonResponse
    {
        $event = $service->recordGameOfferEvent($request->validated());

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $event->id,
                'eventType' => $event->event_type,
            ],
        ], 201);
    }
}
