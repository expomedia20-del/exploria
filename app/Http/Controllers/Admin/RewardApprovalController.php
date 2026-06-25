<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\RewardDefinition;
use App\Services\HubManagerAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RewardApprovalController extends Controller
{
    public function approve(Request $request, RewardDefinition $reward, HubManagerAccessService $access): JsonResponse|RedirectResponse
    {
        $access->ensureCanReviewReward($request->user(), $reward);

        $reward->update([
            'status' => RecordStatus::Active,
            'metadata' => [
                ...($reward->metadata ?? []),
                'approval_status' => 'approved',
                'approved_by_user_id' => $request->user()?->id,
                'approved_at' => now()->toIso8601String(),
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $this->serialize($reward->fresh())]);
        }

        return back()->with('success', 'پیشنهاد فروشگاه تایید و فعال شد.');
    }

    public function reject(Request $request, RewardDefinition $reward, HubManagerAccessService $access): JsonResponse|RedirectResponse
    {
        $access->ensureCanReviewReward($request->user(), $reward);

        $reward->update([
            'status' => RecordStatus::Inactive,
            'metadata' => [
                ...($reward->metadata ?? []),
                'approval_status' => 'rejected',
                'rejected_by_user_id' => $request->user()?->id,
                'rejected_at' => now()->toIso8601String(),
            ],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $this->serialize($reward->fresh())]);
        }

        return back()->with('success', 'پیشنهاد فروشگاه رد شد.');
    }

    /** @return array<string, mixed> */
    private function serialize(?RewardDefinition $reward): array
    {
        return [
            'id' => $reward?->id,
            'code' => $reward?->code,
            'name' => $reward?->name,
            'status' => $reward?->status->value,
            'approvalStatus' => $reward?->metadata['approval_status'] ?? null,
        ];
    }
}
