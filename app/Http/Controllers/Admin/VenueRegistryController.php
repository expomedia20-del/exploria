<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VenueRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VenueRegistryController extends Controller
{
    public function page(Request $request, VenueRegistryService $service): Response
    {
        return Inertia::render('admin/venues/index', [
            'venues' => $service->list($request->user()),
        ]);
    }

    public function index(Request $request, VenueRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list($request->user())]);
    }
}
