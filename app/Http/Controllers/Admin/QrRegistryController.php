<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QrRegistryService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class QrRegistryController extends Controller
{
    public function page(QrRegistryService $service): Response
    {
        return Inertia::render('admin/qr-codes/index', [
            'qrCodes' => $service->list(),
        ]);
    }

    public function index(QrRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list()]);
    }
}
