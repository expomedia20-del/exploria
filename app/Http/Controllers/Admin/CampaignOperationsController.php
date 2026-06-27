<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CampaignOperationsBlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignOperationsController extends Controller
{
    public function page(Request $request, CampaignOperationsBlueprintService $service): Response
    {
        return Inertia::render('admin/campaign-operations/index', $service->overview($request->user()));
    }

    public function index(Request $request, CampaignOperationsBlueprintService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user())]);
    }
}