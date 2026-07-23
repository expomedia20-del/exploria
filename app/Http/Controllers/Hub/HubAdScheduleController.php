<?php

namespace App\Http\Controllers\Hub;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\AdPlacement;
use App\Models\AdRequest;
use App\Models\DisplayDevice;
use App\Services\HubManagerAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HubAdScheduleController extends Controller
{
    public function store(Request $request, AdRequest $adRequest, HubManagerAccessService $access): JsonResponse|RedirectResponse
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
            ->where('id', $data['display_device_id'])
            ->where('status', RecordStatus::Active)
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

        $placement->update([
            'display_device_id' => $displayDevice->id,
            'status' => 'scheduled',
            'starts_at' => $data['starts_at'] ?? $placement->starts_at,
            'ends_at' => $data['ends_at'] ?? $placement->ends_at,
            'priority' => $data['priority'] ?? $placement->priority,
            'metadata' => [
                ...($placement->metadata ?? []),
                'scheduled_by_user_id' => $request->user()?->id,
                'scheduled_at' => now()->toIso8601String(),
                'source' => 'hub_manager_dashboard',
            ],
        ]);

        $placement = $placement->fresh(['displayDevice:id,code,name,device_type']);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => $this->serialize($placement),
            ]);
        }

        return back()->with('success', 'تبلیغ روی نمایشگر زمان‌بندی شد.');
    }

    public function cancel(Request $request, AdPlacement $adPlacement, HubManagerAccessService $access): JsonResponse|RedirectResponse
    {
        $adPlacement->load(['displayDevice:id,hub_id,code,name,device_type']);

        if (! $adPlacement->displayDevice) {
            throw ValidationException::withMessages([
                'ad_placement' => 'این تبلیغ روی نمایشگر زمان‌بندی نشده است.',
            ]);
        }

        $access->ensureCanManageDisplayDevice($request->user(), $adPlacement->displayDevice);

        $adPlacement->update([
            'display_device_id' => null,
            'status' => 'approved',
            'metadata' => [
                ...($adPlacement->metadata ?? []),
                'cancelled_by_user_id' => $request->user()?->id,
                'cancelled_at' => now()->toIso8601String(),
            ],
        ]);

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
