<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CampaignBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CampaignBuilderController extends Controller
{
    public function page(Request $request, CampaignBuilderService $service): Response
    {
        return Inertia::render('admin/campaign-builder/index', $service->overview(
            $request->user(),
            $request->query('campaign'),
        ));
    }

    public function activate(Request $request, string $campaign, CampaignBuilderService $service): JsonResponse|RedirectResponse
    {
        $activated = $service->activate($request->user(), $campaign);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => ['id' => $activated->id, 'code' => $activated->code]]);
        }

        return back()->with('success', 'کمپین برای اجرا فعال شد.');
    }
}
