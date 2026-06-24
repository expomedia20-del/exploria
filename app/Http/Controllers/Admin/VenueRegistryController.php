<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VenueRegistryService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class VenueRegistryController extends Controller
{
    public function page(VenueRegistryService $service): Response
    {
        return Inertia::render('admin/venues/index', [
            'venues' => $service->list(),
        ]);
    }

    public function index(VenueRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list()]);
    }
}
