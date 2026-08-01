<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RecordAdminAuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateMarketingLeadStatusRequest;
use App\Models\MarketingLead;
use App\Models\User;
use App\Services\MarketingLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketingLeadInboxController extends Controller
{
    public function page(Request $request, MarketingLeadService $service): Response
    {
        $status = $this->statusFilter($request);

        return Inertia::render('admin/marketing-leads/index', [
            'leads' => $service->list($status),
            'stats' => $service->stats(),
            'statusOptions' => $service->statusOptions(),
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function index(Request $request, MarketingLeadService $service): JsonResponse
    {
        $status = $this->statusFilter($request);

        return response()->json([
            'data' => $service->list($status),
            'stats' => $service->stats(),
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    public function updateStatus(
        UpdateMarketingLeadStatusRequest $request,
        MarketingLead $lead,
        MarketingLeadService $service,
        RecordAdminAuditAction $audit
    ): JsonResponse|RedirectResponse {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        $lead = $service->updateStatus($lead, $request->payload());
        $audit->execute($user, 'marketing_lead.status_updated', 'marketing_lead', $lead->id, $request->session()->getId(), [
            'status' => $lead->status,
            'audience_type' => $lead->audience_type,
            'source_path' => $lead->source_path,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $service->serialize($lead),
            ]);
        }

        return back()->with('success', 'وضعیت درخواست دمو به‌روزرسانی شد.');
    }

    private function statusFilter(Request $request): ?string
    {
        $status = $request->query('status');

        return is_string($status) && in_array($status, MarketingLeadService::STATUSES, true)
            ? $status
            : null;
    }
}
