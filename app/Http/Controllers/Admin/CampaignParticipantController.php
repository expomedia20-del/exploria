<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CampaignParticipantRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignParticipantController extends Controller
{
    public function page(Request $request, CampaignParticipantRegistryService $service): Response
    {
        return Inertia::render('admin/campaign-participants/index', $service->overview($request->user()));
    }

    public function index(Request $request, CampaignParticipantRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user())]);
    }
}