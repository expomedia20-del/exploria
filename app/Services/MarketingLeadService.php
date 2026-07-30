<?php

namespace App\Services;

use App\Models\MarketingLead;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketingLeadService
{
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
}
