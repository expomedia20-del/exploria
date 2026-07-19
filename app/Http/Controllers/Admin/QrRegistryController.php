<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RecordAdminAuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQrCodeRequest;
use App\Models\QrCode;
use App\Services\CampaignRegistryService;
use App\Services\QrRegistryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrRegistryController extends Controller
{
    public function page(Request $request, QrRegistryService $service, CampaignRegistryService $campaigns): Response
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));

        return Inertia::render('admin/qr-codes/index', [
            'qrCodes' => $service->list($selectedCampaign['id'] ?? null),
            'formOptions' => $service->formOptions(),
            'selectedCampaign' => $selectedCampaign,
        ]);
    }

    public function index(Request $request, QrRegistryService $service, CampaignRegistryService $campaigns): JsonResponse
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));

        return response()->json(['status' => 'success', 'data' => $service->list($selectedCampaign['id'] ?? null)]);
    }

    public function store(StoreQrCodeRequest $request, QrRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $isUpdate = $request->filled('qr_code_id');
        $qrCode = $service->create($request->validated());
        $audit->execute($request->user(), $isUpdate ? 'qr_updated' : 'qr_created', 'qr_code', $qrCode->id, $request->session()->getId(), [
            'code' => $qrCode->code,
            'status' => $qrCode->status->value,
        ]);

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

    public function destroy(Request $request, QrCode $qrCode, QrRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $qrId = $qrCode->id;
        $qrCodeValue = $qrCode->code;
        $service->delete($qrCode);
        $audit->execute($request->user(), 'qr_deleted', 'qr_code', $qrId, $request->session()->getId(), ['code' => $qrCodeValue]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'QR حذف شد.');
    }
}
