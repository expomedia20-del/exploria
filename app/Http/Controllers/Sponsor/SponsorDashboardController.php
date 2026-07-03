<?php

namespace App\Http\Controllers\Sponsor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sponsor\StoreSponsorProposalRequest;
use App\Models\SponsorProposal;
use App\Services\SponsorPortalService;
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

    public function updateProposal(StoreSponsorProposalRequest $request, SponsorProposal $proposal, SponsorPortalService $service): JsonResponse|RedirectResponse
    {
        $proposal = $service->reviseProposal($request->user(), $proposal, $request->validated());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $proposal->id, 'status' => $proposal->status]]);
        }

        return back()->with('success', 'اصلاحات پیشنهاد برای بررسی دوباره ادمین ارسال شد.');
    }
}
