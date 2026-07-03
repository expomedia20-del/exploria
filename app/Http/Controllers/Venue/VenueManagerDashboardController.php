<?php

namespace App\Http\Controllers\Venue;

use App\Http\Controllers\Controller;
use App\Services\VenueManagerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VenueManagerDashboardController extends Controller
{
    public function page(Request $request, VenueManagerDashboardService $service): Response
    {
        return Inertia::render('venue/dashboard', $service->overview($request->user()));
    }

    public function index(Request $request, VenueManagerDashboardService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user())]);
    }
}
