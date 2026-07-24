<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\AdPlacement;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Services\AdminDisplayOperationsService;
use App\Services\HubManagerAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HubAdScheduleController extends Controller
{
    public function store(Request $request, AdRequest $adRequest, HubManagerAccessService $access, AdminDisplayOperationsService $operations): JsonResponse|RedirectResponse
    {
        $access->ensureCanScheduleAdRequest($request->user(), $adRequest);

        $data = $request->validate([
            'display_device_id' => ['required', 'uuid', 'exists:display_devices,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        if ($adRequest->status !== 'approved') {
            throw ValidationException::withMessages([
                'ad_request' => 'فقط تبلیغ تاییدشده قابل زمان‌بندی روی نمایشگر است.',
            ]);
        }

        $displayDevice = DisplayDevice::query()
            ->whereKey($data['display_device_id'])
            ->firstOrFail();

        $access->ensureCanManageDisplayDevice($request->user(), $displayDevice);

        $placement = $adRequest->placements()
            ->where('placement_type', $displayDevice->device_type)
            ->first();

        if (! $placement) {
            throw ValidationException::withMessages([
                'display_device_id' => 'نوع نمایشگر با جایگاه درخواستی تبلیغ هم‌خوان نیست.',
            ]);
        }

        $placement = $operations->schedulePlacement($request->user(), $placement, $data);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $this->serialize($placement),
            ]);
        }

        return back()->with('success', 'تبلیغ روی نمایشگر زمان‌بندی شد.');
    }

    public function cancel(Request $request, AdPlacement $adPlacement, HubManagerAccessService $access, AdminDisplayOperationsService $operations): JsonResponse|RedirectResponse
    {
        $adPlacement->load(['displayDevice:id,hub_id,code,name,device_type']);

        if (! $adPlacement->displayDevice) {
            throw ValidationException::withMessages([
                'ad_placement' => 'این تبلیغ روی نمایشگر زمان‌بندی نشده است.',
            ]);
        }

        $access->ensureCanManageDisplayDevice($request->user(), $adPlacement->displayDevice);

        $adPlacement = $operations->cancelPlacement($request->user(), $adPlacement);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $this->serialize($adPlacement->fresh()),
            ]);
        }

        return back()->with('success', 'زمان‌بندی تبلیغ از نمایشگر لغو شد.');
    }

    /** @return array<string, mixed> */
    private function serialize(?AdPlacement $placement): array
    {
        return [
            'id' => $placement?->id,
            'displayDeviceId' => $placement?->display_device_id,
            'displayDeviceCode' => $placement?->displayDevice?->code,
            'displayDeviceName' => $placement?->displayDevice?->name,
            'placementType' => $placement?->placement_type,
            'status' => $placement?->status,
            'priority' => $placement?->priority,
            'startsAt' => $placement?->starts_at?->toIso8601String(),
            'endsAt' => $placement?->ends_at?->toIso8601String(),
        ];
    }
}
