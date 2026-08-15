<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Events\RecordAdminAuditAction;
use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewRewardRequest;
use App\Models\RewardDefinition;
use App\Services\HubManagerAccessService;
use App\Services\RewardGovernanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RewardApprovalController extends Controller
{
    public function approve(ReviewRewardRequest $request, RewardDefinition $reward, HubManagerAccessService $access, RewardGovernanceService $governance, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $access->ensureCanReviewReward($request->user(), $reward);
        $data = $request->validated();

        $reward = $governance->activate($reward, $request->user(), $data['notes'] ?? null);
        $this->audit($request, $reward, $audit, 'reward_approved');

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $this->serialize($reward->fresh())]);
        }

        return back()->with('success', 'پیشنهاد فروشگاه تایید و فعال شد.');
    }

    public function reject(ReviewRewardRequest $request, RewardDefinition $reward, HubManagerAccessService $access, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $access->ensureCanReviewReward($request->user(), $reward);
        $data = $request->validated();

        $reward->update([
            'status' => RecordStatus::Inactive,
            'metadata' => [
                ...($reward->metadata ?? []),
                'approval_status' => 'rejected',
                'rejected_by_user_id' => $request->user()?->id,
                'rejected_at' => now()->toIso8601String(),
                'review_notes' => $data['notes'] ?? null,
            ],
        ]);
        $this->audit($request, $reward, $audit, 'reward_rejected');

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $this->serialize($reward->fresh())]);
        }

        return back()->with('success', 'پیشنهاد فروشگاه رد شد.');
    }

    public function requestRevision(ReviewRewardRequest $request, RewardDefinition $reward, HubManagerAccessService $access, RecordAdminAuditAction $audit): JsonResponse|RedirectResponse
    {
        $access->ensureCanReviewReward($request->user(), $reward);
        $data = $request->validated();

        $reward->update([
            'status' => RecordStatus::Draft,
            'metadata' => [
                ...($reward->metadata ?? []),
                'approval_status' => 'revision_requested',
                'revision_requested_by_user_id' => $request->user()?->id,
                'revision_requested_at' => now()->toIso8601String(),
                'review_notes' => $data['notes'] ?? null,
            ],
        ]);
        $this->audit($request, $reward, $audit, 'reward_revision_requested');

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'data' => $this->serialize($reward->fresh())]);
        }

        return back()->with('success', 'پیشنهاد برای اصلاح به فروشگاه/اسپانسر برگردانده شد.');
    }

    private function audit(ReviewRewardRequest $request, RewardDefinition $reward, RecordAdminAuditAction $audit, string $action): void
    {
        $audit->execute($request->user(), $action, 'reward', $reward->id, $request->session()->getId(), [
            'code' => $reward->code,
            'name' => $reward->name,
            'status' => $reward->status->value,
            'decision' => $reward->metadata['approval_status'] ?? null,
            'cost_owner_financial_account_id' => $reward->cost_owner_financial_account_id,
            'inventory_mode' => $reward->inventory_mode?->value,
            'available_from' => $reward->available_from?->toIso8601String(),
            'available_until' => $reward->available_until?->toIso8601String(),
            'expires_after_minutes' => $reward->expires_after_minutes,
            'per_user_award_limit' => $reward->per_user_award_limit,
        ]);
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
            'reviewNotes' => $reward?->metadata['review_notes'] ?? null,
        ];
    }
}
