<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdPlacement;
use App\Models\User;
use App\Services\AdminDisplayOperationsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DisplayOperationsController extends Controller
{
    public function page(AdminDisplayOperationsService $service): Response
    {
        return Inertia::render('admin/display-operations/index', $service->overview());
    }

    public function index(AdminDisplayOperationsService $service): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $service->overview()]);
    }

    public function schedule(Request $request, AdPlacement $adPlacement, AdminDisplayOperationsService $service): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'display_device_id' => ['required', 'uuid', 'exists:display_devices,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $placement = $service->schedulePlacement($user, $adPlacement, $data);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $service->serializePlacement($placement),
            ]);
        }

        return back()->with('success', 'تبلیغ روی نمایشگر انتخاب‌شده زمان‌بندی شد.');
    }

    public function cancel(Request $request, AdPlacement $adPlacement, AdminDisplayOperationsService $service): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $placement = $service->cancelPlacement($user, $adPlacement);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $service->serializePlacement($placement),
            ]);
        }

        return back()->with('success', 'زمان‌بندی تبلیغ از نمایشگر لغو شد.');
    }
}
