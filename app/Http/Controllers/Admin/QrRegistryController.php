<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQrCodeRequest;
use App\Services\QrRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class QrRegistryController extends Controller
{
    public function page(QrRegistryService $service): Response
    {
        return Inertia::render('admin/qr-codes/index', [
            'qrCodes' => $service->list(),
            'formOptions' => $service->formOptions(),
        ]);
    }

    public function index(QrRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list()]);
    }

    public function store(StoreQrCodeRequest $request, QrRegistryService $service): JsonResponse|RedirectResponse
    {
        $qrCode = $service->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $qrCode->id,
                    'code' => $qrCode->code,
                    'destinationUrl' => $qrCode->destination_url,
                ],
            ], 201);
        }

        return back()->with('success', 'QR جدید ثبت شد.');
    }
}
