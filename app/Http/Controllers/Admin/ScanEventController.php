<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ScanEventIndexRequest;
use App\Services\ScanEventRegistryService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ScanEventController extends Controller
{
    public function page(ScanEventIndexRequest $request, ScanEventRegistryService $registry): Response
    {
        return Inertia::render('admin/events/index', $registry->registry($request->validated('result')));
    }

    public function index(ScanEventIndexRequest $request, ScanEventRegistryService $registry): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $registry->registry($request->validated('result'))]);
    }
}
