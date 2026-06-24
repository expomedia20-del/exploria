<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PartnerRegistryService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PartnerRegistryController extends Controller
{
    public function page(PartnerRegistryService $service): Response
    {
        return Inertia::render('admin/partners/index', [
            'partners' => $service->list(),
        ]);
    }

    public function index(PartnerRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list()]);
    }
}
