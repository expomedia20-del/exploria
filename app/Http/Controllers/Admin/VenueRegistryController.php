<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateVenueProfileRequest;
use App\Models\Venue;
use App\Services\VenueRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function updateProfile(UpdateVenueProfileRequest $request, Venue $venue, VenueRegistryService $service): JsonResponse|RedirectResponse
    {
        $service->updateProfile($venue, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'شناخت‌نامه مکان ذخیره شد.');
    }
}
