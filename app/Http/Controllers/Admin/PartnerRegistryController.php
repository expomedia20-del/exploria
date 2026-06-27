<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PartnerRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerRegistryController extends Controller
{
    public function page(Request $request, PartnerRegistryService $service): Response
    {
        return Inertia::render('admin/partners/index', [
            'partners' => $service->list($request->user()),
        ]);
    }

    public function index(Request $request, PartnerRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list($request->user())]);
    }
}
