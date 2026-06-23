<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QrRegistryService;
use Illuminate\Http\JsonResponse;

class QrRegistryController extends Controller
{
    public function index(QrRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list()]);
    }
}
