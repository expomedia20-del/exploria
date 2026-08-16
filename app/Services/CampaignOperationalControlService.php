<?php

namespace App\Services;

use App\Actions\Events\RecordAdminAuditAction;
use App\Enums\RecordStatus;
use App\Models\Campaign;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CampaignOperationalControlService
{
    public function __construct(private readonly RecordAdminAuditAction $audit) {}

    /** @param array{reason: string, incident_reference: string} $data */
    public function pause(Campaign $campaign, User $actor, string $sessionId, array $data): Campaign
    {
        return DB::transaction(function () use ($actor, $campaign, $data, $sessionId): Campaign {
            $lockedCampaign = Campaign::query()->lockForUpdate()->findOrFail($campaign->id);
            $metadata = $lockedCampaign->metadata ?? [];

            if (($metadata['lifecycle_state'] ?? null) === 'archived') {
                throw ValidationException::withMessages(['campaign' => 'کمپین آرشیوشده را نمی‌توان وارد توقف عملیاتی کرد.']);
            }

            if ($lockedCampaign->isOperationallyPaused()) {
                throw ValidationException::withMessages(['campaign' => 'این کمپین هم‌اکنون در توقف عملیاتی است.']);
            }

            if ($lockedCampaign->status !== RecordStatus::Active) {
                throw ValidationException::withMessages(['campaign' => 'فقط کمپین فعال را می‌توان به‌صورت عملیاتی متوقف کرد.']);
            }

            $pausedAt = now()->toIso8601String();
            $control = [
                'state' => 'paused',
                'scope' => 'campaign',
                'reason' => $data['reason'],
                'incident_reference' => $data['incident_reference'],
                'paused_by_user_id' => $actor->id,
                'paused_by_name' => $actor->name,
                'paused_at' => $pausedAt,
                'status_before_pause' => $lockedCampaign->status->value,
                'qr_coordination' => 'blocked_by_campaign_status',
                'mission_coordination' => 'blocked_by_campaign_status',
                'reward_issuance_coordination' => 'blocked_by_campaign_status',
            ];

            $metadata['operational_control'] = $control;
            $lockedCampaign->update([
                'status' => RecordStatus::Inactive,
                'metadata' => $metadata,
            ]);

            $this->audit->execute(
                $actor,
                'campaign_paused',
                'campaign',
                $lockedCampaign->id,
                $sessionId,
                [
                    ...$control,
                    'affected_qr_count' => $lockedCampaign->qrCodes()->count(),
                    'status_after_pause' => RecordStatus::Inactive->value,
                ],
                ['venue_id' => $lockedCampaign->venue_id, 'campaign_id' => $lockedCampaign->id],
            );

            return $lockedCampaign->refresh();
        });
    }

    /**
     * @param array{
     *     incident_reference: string,
     *     corrective_action: string,
     *     recovery_evidence: string,
     *     approval_note: string,
     *     approval_confirmed: bool
     * } $data
     */
    public function resume(Campaign $campaign, User $actor, string $sessionId, array $data): Campaign
    {
        return DB::transaction(function () use ($actor, $campaign, $data, $sessionId): Campaign {
            $lockedCampaign = Campaign::query()->lockForUpdate()->findOrFail($campaign->id);
            $metadata = $lockedCampaign->metadata ?? [];
            $control = $lockedCampaign->operationalControl();

            if (($metadata['lifecycle_state'] ?? null) === 'archived') {
                throw ValidationException::withMessages(['campaign' => 'کمپین آرشیوشده را نمی‌توان از توقف عملیاتی خارج کرد.']);
            }

            if (! $control || ($control['state'] ?? null) !== 'paused' || $lockedCampaign->status !== RecordStatus::Inactive) {
                throw ValidationException::withMessages(['campaign' => 'این کمپین در وضعیت معتبر توقف عملیاتی نیست.']);
            }

            if (! hash_equals((string) ($control['incident_reference'] ?? ''), $data['incident_reference'])) {
                throw ValidationException::withMessages(['incident_reference' => 'مرجع رخداد با توقف ثبت‌شده مطابقت ندارد.']);
            }

            if (! $data['approval_confirmed']) {
                throw ValidationException::withMessages(['approval_confirmed' => 'تأیید صریح مسئول مجاز برای ازسرگیری الزامی است.']);
            }

            $resumedAt = now()->toIso8601String();
            $restoredStatus = RecordStatus::tryFrom((string) ($control['status_before_pause'] ?? '')) ?? RecordStatus::Draft;

            if ($restoredStatus !== RecordStatus::Active) {
                throw ValidationException::withMessages(['campaign' => 'وضعیت پیش از توقف برای ازسرگیری معتبر نیست.']);
            }

            $control = [
                ...$control,
                'state' => 'resumed',
                'corrective_action' => $data['corrective_action'],
                'recovery_evidence' => $data['recovery_evidence'],
                'approval_note' => $data['approval_note'],
                'resume_approved_by_user_id' => $actor->id,
                'resume_approved_by_name' => $actor->name,
                'resumed_by_user_id' => $actor->id,
                'resumed_at' => $resumedAt,
                'restored_status' => $restoredStatus->value,
            ];

            $metadata['operational_control'] = $control;
            $lockedCampaign->update([
                'status' => $restoredStatus,
                'metadata' => $metadata,
            ]);

            $this->audit->execute(
                $actor,
                'campaign_resumed',
                'campaign',
                $lockedCampaign->id,
                $sessionId,
                $control,
                ['venue_id' => $lockedCampaign->venue_id, 'campaign_id' => $lockedCampaign->id],
            );

            return $lockedCampaign->refresh();
        });
    }
}
