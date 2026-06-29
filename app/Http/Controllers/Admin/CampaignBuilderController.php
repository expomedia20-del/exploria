<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CampaignBuilderService;
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
}
