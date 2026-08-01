<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RecordAdminAuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignRequest;
use App\Models\Campaign;
use App\Services\CampaignRegistryService;
use App\Services\MissionRewardBlueprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignRegistryController extends Controller
{
    public function page(Request $request, CampaignRegistryService $service, MissionRewardBlueprintService $blueprints): Response
    {
        $archiveMode = $request->boolean('archived');

        return Inertia::render('admin/campaigns/index', [
            'campaigns' => $service->list($request->user(), $archiveMode),
            'archiveMode' => $archiveMode,
            'archivedCampaignsCount' => $service->archivedCount($request->user()),
            'venueOptions' => $service->venueOptions($request->user()),
            'selectedCampaign' => $service->context($request->user(), $request->query('campaign')),
            'selectedVenue' => $service->venueContext($request->user(), $request->query('venue')),
            'selectedBlueprint' => $blueprints->handoff($request->query('blueprint')),
        ]);
    }

    public function index(Request $request, CampaignRegistryService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->list($request->user(), $request->boolean('archived'))]);
    }

    public function store(StoreCampaignRequest $request, CampaignRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $campaign = $service->create($validated);
        $audit->execute($request->user(), ! empty($validated['campaign_id']) ? 'campaign_updated' : 'campaign_created', 'campaign', $campaign->id, $request->session()->getId(), [
            'code' => $campaign->code,
            'name' => $campaign->name,
            'status' => $campaign->status->value,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $campaign->id,
                    'code' => $campaign->code,
                    'name' => $campaign->name,
                ],
            ], 201);
        }

        if (! empty($validated['campaign_id'])) {
            return back()->with('success', 'کمپین ویرایش شد.');
        }

        return redirect()
            ->route('admin.campaign-builder.page', array_filter([
                'campaign' => $campaign->code,
                'blueprint' => $campaign->metadata['blueprint_code'] ?? null,
                'blueprint_action' => 'build',
            ]))
            ->with('success', 'کمپین جدید ثبت شد.');
    }

    public function destroy(Request $request, Campaign $campaign, CampaignRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $campaignId = $campaign->id;
        $payload = ['code' => $campaign->code, 'name' => $campaign->name];
        $service->delete($campaign);
        $audit->execute($request->user(), 'campaign_deleted', 'campaign', $campaignId, $request->session()->getId(), $payload);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success']);
        }

        return back()->with('success', 'کمپین حذف شد.');
    }

    public function archive(Request $request, Campaign $campaign, CampaignRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $archived = $service->archive($campaign, $request->user());
        $audit->execute($request->user(), 'campaign_archived', 'campaign', $archived->id, $request->session()->getId(), [
            'code' => $archived->code,
            'name' => $archived->name,
            'archived_at' => $archived->metadata['archived_at'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $archived->id, 'isArchived' => true]]);
        }

        return back()->with('success', 'کمپین به آرشیو منتقل شد و از فهرست اصلی خارج شد.');
    }

    public function restore(Request $request, Campaign $campaign, CampaignRegistryService $service, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $restored = $service->restore($campaign);
        $audit->execute($request->user(), 'campaign_restored', 'campaign', $restored->id, $request->session()->getId(), [
            'code' => $restored->code,
            'name' => $restored->name,
            'status' => $restored->status->value,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $restored->id, 'isArchived' => false]]);
        }

        return back()->with('success', 'کمپین از آرشیو برگشت و دوباره در فهرست اصلی دیده می‌شود.');
    }
}
