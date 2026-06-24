<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\PartnerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerDashboardController extends Controller
{
    public function page(Request $request, PartnerDashboardService $service): Response
    {
        return Inertia::render('partner/dashboard', $service->overview($request->user()));
    }

    public function index(Request $request, PartnerDashboardService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user())]);
    }
}
