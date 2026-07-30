<?php

namespace App\Services;

use App\Models\MarketingLead;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MarketingLeadService
{
    /** @var array<int, string> */
    public const STATUSES = ['new', 'reviewing', 'demo_scheduled', 'closed'];

    /** @param array<string, mixed> $data */
    public function create(array $data, Request $request): MarketingLead
    {
        return MarketingLead::query()->create([
            'audience_type' => (string) $data['audience_type'],
            'organization_name' => $this->nullableText($data['organization_name'] ?? null),
            'contact_name' => trim((string) $data['contact_name']),
            'mobile' => trim((string) $data['mobile']),
            'city' => $this->nullableText($data['city'] ?? null),
            'project_hint' => $this->nullableText($data['project_hint'] ?? null),
            'notes' => $this->nullableText($data['notes'] ?? null),
            'status' => 'new',
            'source_path' => $this->sourcePath($data['source_path'] ?? null, $request),
            'metadata' => [
                'source' => 'public_marketing_landing',
                'ip_hash' => $request->ip() ? hash('sha256', $request->ip()) : null,
                'user_agent_hash' => $request->userAgent() ? hash('sha256', $request->userAgent()) : null,
            ],
        ]);
    }

    /** @return array{total: int, new: int, reviewing: int, demo_scheduled: int, closed: int} */
    public function stats(): array
    {
        $counts = MarketingLead::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'total' => MarketingLead::query()->count(),
            'new' => (int) ($counts['new'] ?? 0),
            'reviewing' => (int) ($counts['reviewing'] ?? 0),
            'demo_scheduled' => (int) ($counts['demo_scheduled'] ?? 0),
            'closed' => (int) ($counts['closed'] ?? 0),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function list(?string $status = null): Collection
    {
        return MarketingLead::query()
            ->when(
                in_array($status, self::STATUSES, true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest()
            ->get()
            ->map(fn (MarketingLead $lead): array => $this->serialize($lead));
    }

    /** @param array{status: string, internal_notes?: string|null} $data */
    public function updateStatus(MarketingLead $lead, array $data): MarketingLead
    {
        $metadata = $lead->metadata ?? [];
        $metadata['internal_notes'] = $this->nullableText($data['internal_notes'] ?? null);
        $metadata['last_reviewed_at'] = now()->toIso8601String();

        $lead->forceFill([
            'status' => $data['status'],
            'metadata' => $metadata,
        ])->save();

        return $lead->refresh();
    }

    /** @return array<string, mixed> */
    public function serialize(MarketingLead $lead): array
    {
        return [
            'id' => $lead->id,
            'audienceType' => $lead->audience_type,
            'audienceLabel' => $this->audienceLabel($lead->audience_type),
            'organizationName' => $lead->organization_name,
            'contactName' => $lead->contact_name,
            'mobile' => $lead->mobile,
            'city' => $lead->city,
            'projectHint' => $lead->project_hint,
            'notes' => $lead->notes,
            'status' => $lead->status,
            'statusLabel' => $this->statusLabel($lead->status),
            'sourcePath' => $lead->source_path,
            'internalNotes' => $lead->metadata['internal_notes'] ?? null,
            'createdAt' => $lead->created_at?->toIso8601String(),
            'createdAtLabel' => $lead->created_at?->timezone('Asia/Tehran')->format('Y/m/d H:i'),
            'updatedAt' => $lead->updated_at?->toIso8601String(),
        ];
    }

    /** @return array<string, string> */
    public function statusOptions(): array
    {
        return collect(self::STATUSES)
            ->mapWithKeys(fn (string $status): array => [$status => $this->statusLabel($status)])
            ->all();
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function sourcePath(mixed $value, Request $request): string
    {
        $path = $this->nullableText($value) ?? $request->headers->get('referer') ?? '/';

        return Str::limit($path, 255, '');
    }

    private function statusLabel(string $status): string
    {
        return [
            'new' => 'جدید',
            'reviewing' => 'در حال پیگیری',
            'demo_scheduled' => 'دمو زمان‌بندی شد',
            'closed' => 'بسته شد',
        ][$status] ?? $status;
    }

    private function audienceLabel(string $audienceType): string
    {
        return [
            'venue' => 'مکان یا مجموعه',
            'commercial_unit' => 'واحد تجاری',
            'sponsor' => 'اسپانسر',
            'visitor_growth' => 'جذب بازدیدکننده',
            'other' => 'سایر',
        ][$audienceType] ?? $audienceType;
    }
}
