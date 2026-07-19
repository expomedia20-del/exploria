<?php

namespace App\Http\Controllers\Partner;

use App\Actions\Events\RecordAdminAuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\UpdatePartnerProfileRequest;
use App\Services\PartnerDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PartnerDashboardController extends Controller
{
    public function page(Request $request, PartnerDashboardService $service): Response
    {
        return Inertia::render('partner/dashboard', $service->overview($request->user(), $request->query('campaign')));
    }

    public function index(Request $request, PartnerDashboardService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user(), $request->query('campaign'))]);
    }

    public function update(UpdatePartnerProfileRequest $request, PartnerDashboardService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $partner = $service->updateProfile($request->user(), $request->validated());
        $audit->execute($request->user(), 'partner_profile_updated', 'partner', $partner->id, $request->session()->getId(), [
            'code' => $partner->code,
            'name' => $partner->name,
            'status' => $partner->status->value,
        ], ['venue_id' => $partner->venue_id]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $partner->id,
                    'code' => $partner->code,
                    'name' => $partner->name,
                    'contactName' => $partner->contact_name,
                    'contactMobile' => $partner->contact_mobile,
                    'category' => $partner->metadata['category'] ?? null,
                    'displayVisibility' => (bool) ($partner->metadata['display_visibility'] ?? false),
                ],
            ]);
        }

        return back()->with('success', 'اطلاعات فروشگاه ذخیره شد.');
    }
}
