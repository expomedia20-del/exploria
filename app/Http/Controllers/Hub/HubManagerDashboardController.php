<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Services\HubManagerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HubManagerDashboardController extends Controller
{
    public function page(Request $request, HubManagerDashboardService $service): Response
    {
        return Inertia::render('hub/dashboard', $service->overview($request->user(), $request->routeIs('ravaq.*')));
    }

    public function index(Request $request, HubManagerDashboardService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user(), $request->routeIs('ravaq.*'))]);
    }
}
