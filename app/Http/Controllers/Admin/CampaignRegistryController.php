<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Services\CampaignRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignRegistryController extends Controller
{
    public function page(Request $request, CampaignRegistryService $service): Response
    {
        return Inertia::render('admin/campaigns/index', [
            'campaigns' => $service->list($request->user()),
            'venueOptions' => $service->venueOptions($request->user()),
        ]);
    }

    public function index(Request $request, CampaignRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list($request->user())]);
    }

    public function store(StoreCampaignRequest $request, CampaignRegistryService $service): JsonResponse|RedirectResponse
    {
        $campaign = $service->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $campaign->id,
                    'code' => $campaign->code,
                    'name' => $campaign->name,
                ],
            ], 201);
        }

        return back()->with('success', 'کمپین جدید ثبت شد.');
    }
}
