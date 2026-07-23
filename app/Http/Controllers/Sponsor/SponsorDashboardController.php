<?php

namespace App\Http\Controllers\Sponsor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\StoreAdRequestRequest;
use App\Http\Requests\Sponsor\StoreSponsorProposalRequest;
use App\Models\SponsorProposal;
use App\Services\SponsorPortalService;
use App\Services\StandaloneAdvertisingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SponsorDashboardController extends Controller
{
    public function page(Request $request, SponsorPortalService $service): Response
    {
        return Inertia::render('sponsor/dashboard', $service->overview($request->user()));
    }

    public function index(Request $request, SponsorPortalService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview($request->user())]);
    }

    public function storeProposal(StoreSponsorProposalRequest $request, SponsorPortalService $service): JsonResponse|RedirectResponse
    {
        $proposal = $service->submitProposal($request->user(), $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $proposal->id]], 201);
        }

        return back()->with('success', 'پیشنهاد اسپانسری برای بررسی ادمین ارسال شد.');
    }

    public function storeAdRequest(StoreAdRequestRequest $request, SponsorPortalService $portal, StandaloneAdvertisingService $advertising): JsonResponse|RedirectResponse
    {
        $partner = $portal->sponsorPartnerForUser($request->user());
        $adRequest = $advertising->createSponsorAdRequest($request->user(), $partner, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $adRequest->id,
                    'code' => $adRequest->code,
                    'title' => $adRequest->title,
                    'status' => $adRequest->status,
                    'advertiserType' => $adRequest->advertiser_type,
                ],
            ], 201);
        }

        return back()->with('success', 'درخواست تبلیغ اسپانسری برای بررسی تیم اکسپلوریا ارسال شد.');
    }

    public function updateProposal(StoreSponsorProposalRequest $request, SponsorProposal $proposal, SponsorPortalService $service): JsonResponse|RedirectResponse
    {
        $proposal = $service->reviseProposal($request->user(), $proposal, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $proposal->id, 'status' => $proposal->status]]);
        }

        return back()->with('success', 'اصلاحات پیشنهاد برای بررسی دوباره ادمین ارسال شد.');
    }
}
