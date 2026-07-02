<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCampaignSponsorshipRequest;
use App\Http\Requests\Admin\StoreSponsorAccountRequest;
use App\Http\Requests\Admin\StoreSponsorPartnerAssignmentRequest;
use App\Http\Requests\Admin\UpdateSponsorProposalStatusRequest;
use App\Models\SponsorProposal;
use App\Services\CampaignRegistryService;
use App\Services\SponsorActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SponsorActivationController extends Controller
{
    public function page(Request $request, SponsorActivationService $service, CampaignRegistryService $campaigns): Response
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));
        $data = $service->overview($request->user(), $selectedCampaign['id'] ?? null);
        $data['selectedCampaign'] = $selectedCampaign;

        return Inertia::render('admin/sponsors/index', $data);
    }

    public function index(Request $request, SponsorActivationService $service, CampaignRegistryService $campaigns): JsonResponse
    {
        $selectedCampaign = $campaigns->context($request->user(), $request->query('campaign'));

        return response()->json(['status' => 'success', 'data' => $service->overview($request->user(), $selectedCampaign['id'] ?? null)]);
    }

    public function storeSponsor(StoreSponsorAccountRequest $request, SponsorActivationService $service): JsonResponse|RedirectResponse
    {
        $sponsor = $service->storeSponsor($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $sponsor->id]], 201);
        }

        return back()->with('success', 'حساب اسپانسر ذخیره شد.');
    }

    public function storeSponsorship(StoreCampaignSponsorshipRequest $request, SponsorActivationService $service): JsonResponse|RedirectResponse
    {
        $sponsorship = $service->storeSponsorship($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $sponsorship->id]], 201);
        }

        return back()->with('success', 'حمایت اسپانسری به کمپین متصل شد.');
    }

    public function storePartnerAssignment(StoreSponsorPartnerAssignmentRequest $request, SponsorActivationService $service): JsonResponse|RedirectResponse
    {
        $assignment = $service->storePartnerAssignment($request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $assignment->id]], 201);
        }

        return back()->with('success', 'اتصال اسپانسر به واحد عضو کمپین ذخیره شد.');
    }

    public function updateProposalStatus(UpdateSponsorProposalStatusRequest $request, SponsorProposal $proposal, SponsorActivationService $service): JsonResponse|RedirectResponse
    {
        $proposal = $service->updateProposalStatus($proposal, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $proposal->id, 'status' => $proposal->status]]);
        }

        return back()->with('success', 'وضعیت پیشنهاد اسپانسر به‌روزرسانی شد.');
    }
}
