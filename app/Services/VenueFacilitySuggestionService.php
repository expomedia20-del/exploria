<?php

namespace App\Services;

use App\Models\Venue;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @phpstan-type FacilityCandidate array{name: string, confidence: string, field_review_required: bool}
 * @phpstan-type EnrichedSuggestion array{name: string, function: string, campaignUses: array<int, string>, priority: string, notes: string, confidence: string, fieldReviewRequired: bool, source: string}
 * @phpstan-type FacilitySuggestion array{name: string, function: string, campaignUses: array<int, string>, priority: string, notes: string, confidence: string, fieldReviewRequired: bool, source: string, alreadyExists: bool}
 * @phpstan-type SuggestionResult array{suggestions: array<int, FacilitySuggestion>, summary: array{count: int, newCount: int, existingCount: int, sourceMode: string, needsHumanReview: bool}}
 */
class VenueFacilitySuggestionService
{
    private const MAX_SUGGESTIONS = 40;

    /**
     * @param  array<string, mixed>  $data
     * @return SuggestionResult
     */
    public function suggest(Venue $venue, array $data): array
    {
        $sourceText = $this->sourceText($venue, $data);
        $existingNames = collect($this->profileFacilities($venue))
            ->map(fn (array $facility): string => $this->key((string) ($facility['name'] ?? '')))
            ->filter()
            ->values();
        $source = filled($data['official_website_url'] ?? null)
            ? trim((string) $data['official_website_url'])
            : $this->profileValue($venue, 'official_website_url');

        $suggestions = $this->candidateNames($sourceText)
            ->unique(fn (array $candidate): string => $this->key($candidate['name']))
            ->take(self::MAX_SUGGESTIONS)
            ->map(function (array $candidate) use ($source, $existingNames): array {
                $suggestion = $this->enrich($candidate, $source);
                $suggestion['alreadyExists'] = $existingNames->contains($this->key($candidate['name']));

                return $suggestion;
            })
            ->values()
            ->all();
        $newCount = collect($suggestions)->reject(fn (array $suggestion): bool => (bool) $suggestion['alreadyExists'])->count();

        return [
            'suggestions' => $suggestions,
            'summary' => [
                'count' => count($suggestions),
                'newCount' => $newCount,
                'existingCount' => count($suggestions) - $newCount,
                'sourceMode' => filled(trim($sourceText)) ? 'manual_source_text' : 'empty_source',
                'needsHumanReview' => collect($suggestions)->contains(fn (array $item): bool => (bool) $item['fieldReviewRequired']),
            ],
        ];
    }

    /** @param array<string, mixed> $data */
    public function applySuggestions(Venue $venue, array $data): Venue
    {
        $result = $this->suggest($venue, $data);
        $suggestions = [];

        foreach ($result['suggestions'] as $suggestion) {
            if ($suggestion['alreadyExists']) {
                continue;
            }

            $suggestions[] = [
                'name' => $suggestion['name'],
                'function' => $suggestion['function'],
                'campaignUses' => $suggestion['campaignUses'],
                'priority' => $suggestion['priority'],
                'notes' => $suggestion['notes'],
                'confidence' => $suggestion['confidence'],
                'fieldReviewRequired' => $suggestion['fieldReviewRequired'],
                'source' => $suggestion['source'],
            ];
        }

        $metadata = is_array($venue->metadata) ? $venue->metadata : [];
        $profile = Arr::get($metadata, 'location_profile', []);
        $profile = is_array($profile) ? $profile : [];
        $existing = collect($this->profileFacilities($venue));

        $merged = $existing
            ->merge($suggestions)
            ->unique(fn (array $facility): string => $this->key((string) ($facility['name'] ?? '')))
            ->values()
            ->all();

        $profile['facilities'] = $merged;
        $profile['official_website_url'] = filled($data['official_website_url'] ?? null)
            ? trim((string) $data['official_website_url'])
            : ($profile['official_website_url'] ?? null);
        $profile['updated_at'] = now()->toIso8601String();
        $profile['facility_extraction'] = [
            'last_run_at' => now()->toIso8601String(),
            'suggested_count' => count($suggestions),
            'applied_count' => count($merged) - $existing->count(),
            'source_mode' => $result['summary']['sourceMode'],
        ];

        $venue->update([
            'metadata' => [
                ...$metadata,
                'location_profile' => $profile,
            ],
        ]);

        return $venue->refresh();
    }

    /**
     * @param  FacilityCandidate  $candidate
     * @return EnrichedSuggestion
     */
    private function enrich(array $candidate, ?string $source): array
    {
        $name = $candidate['name'];
        $function = $this->functionFor($name);
        $uses = $this->campaignUsesFor($name, $function);
        $priority = $this->priorityFor($name, $function, $candidate['confidence']);
        $fieldReview = $candidate['field_review_required'] || $candidate['confidence'] !== 'high';

        return [
            'name' => $name,
            'function' => $function,
            'campaignUses' => $uses,
            'priority' => $priority,
            'notes' => $this->notesFor($name, $function, $uses, $candidate['confidence'], $fieldReview),
            'confidence' => $candidate['confidence'],
            'fieldReviewRequired' => $fieldReview,
            'source' => $source ?: 'متن پژوهش دستی',
        ];
    }

    /** @param array<string, mixed> $data */
    private function sourceText(Venue $venue, array $data): string
    {
        return collect([
            $data['source_text'] ?? null,
            $this->profileValue($venue, 'manual_research_notes'),
        ])
            ->filter(fn (mixed $value): bool => filled($value))
            ->map(fn (mixed $value): string => trim((string) $value))
            ->implode("\n");
    }

    /** @return Collection<int, FacilityCandidate> */
    private function candidateNames(string $sourceText): Collection
    {
        $text = html_entity_decode(strip_tags($sourceText), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return collect(preg_split('/\R/u', $text) ?: [])
            ->flatMap(fn (string $line): array => $this->lineCandidates($line))
            ->map(fn (array $candidate): array => [
                ...$candidate,
                'name' => $this->cleanName($candidate['name']),
            ])
            ->filter(fn (array $candidate): bool => $this->isCandidateName($candidate['name']))
            ->values();
    }

    /** @return array<int, FacilityCandidate> */
    private function lineCandidates(string $line): array
    {
        $line = preg_replace('/^[\s\-\*\x{2022}\x{2013}\x{2014}0-9۰-۹\.\(\)\[\]]+/u', '', $line) ?? $line;
        $line = preg_replace('/[\s\-\*\x{2022}\x{2013}\x{2014}0-9۰-۹\.\(\)\[\]]+$/u', '', $line) ?? $line;

        if ($line === '') {
            return [];
        }

        if (preg_match('/[،,]/u', $line) && mb_strlen($line) <= 180) {
            return collect(preg_split('/[،,]+/u', $line) ?: [])
                ->map(fn (string $part): string => trim($part))
                ->filter()
                ->map(fn (string $part): array => [
                    'name' => $part,
                    'confidence' => $this->containsAny($part, $this->facilityTriggers()) ? 'high' : 'medium',
                    'field_review_required' => ! $this->containsAny($part, $this->facilityTriggers()),
                ])
                ->values()
                ->all();
        }

        if (mb_strlen($line) <= 80) {
            return [[
                'name' => $line,
                'confidence' => 'high',
                'field_review_required' => false,
            ]];
        }

        return collect(preg_split('/[.؟!؛]+/u', $line) ?: [])
            ->flatMap(fn (string $sentence): array => preg_split('/[،,]+/u', $sentence) ?: [])
            ->map(fn (string $part): string => trim($part))
            ->filter(fn (string $part): bool => mb_strlen($part) >= 3 && mb_strlen($part) <= 90)
            ->filter(fn (string $part): bool => $this->containsAny($part, $this->facilityTriggers()))
            ->map(fn (string $part): array => [
                'name' => $part,
                'confidence' => 'medium',
                'field_review_required' => true,
            ])
            ->values()
            ->all();
    }

    private function cleanName(string $name): string
    {
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;
        $name = preg_replace('/^(شامل|هم اکنون به عنوان|همچنین|و همچنین|مجموعه)\s+/u', '', $name) ?? $name;

        return preg_replace('/^[\s:：،,؛.;]+|[\s:：،,؛.;]+$/u', '', $name) ?? $name;
    }

    private function isCandidateName(string $name): bool
    {
        if (mb_strlen($name) < 3 || mb_strlen($name) > 90) {
            return false;
        }

        if (! preg_match('/[\p{Arabic}A-Za-z]/u', $name)) {
            return false;
        }

        $blocked = ['صفحه', 'سایت', 'خبر', 'کلیک', 'اینجا', 'دریافت کنید', 'تلفن', 'آدرس'];

        return ! $this->containsAny($name, $blocked);
    }

    private function functionFor(string $name): string
    {
        return match (true) {
            $this->containsAny($name, ['کافه', 'کافی', 'رستوران', 'فود', 'فروشگاه', 'بازار', 'سوغات', 'صنایع دستی', 'shop', 'cafe', 'food']) => 'retail',
            $this->containsAny($name, ['بلیت', 'بلیط', 'پذیرش', 'ورودی', 'آسانسور', 'پارکینگ', 'مسیر', 'گیت', 'راهنما']) => 'route',
            $this->containsAny($name, ['نمایشگر', 'تابلو', 'بیلبورد', 'مانیتورینگ', 'رسانه']) => 'media',
            $this->containsAny($name, ['نماز', 'استراحت', 'انتظار', 'سرویس']) => 'rest',
            $this->containsAny($name, ['بازی', 'کاربازیا', 'شهربازی', 'سینما', 'اسکیت', 'تفریح']) => 'entertainment',
            $this->containsAny($name, ['مدرسه', 'آموزش', 'کتابخانه', 'آمفی', 'کلاس']) => 'education',
            default => 'discovery',
        };
    }

    /** @return array<int, string> */
    private function campaignUsesFor(string $name, string $function): array
    {
        $uses = match ($function) {
            'retail' => ['reward', 'sponsor', 'ad'],
            'route' => ['qr', 'mission'],
            'media' => ['ad', 'display'],
            'rest' => ['reward'],
            'entertainment' => ['qr', 'mission', 'reward', 'sponsor'],
            'education' => ['qr', 'mission', 'display'],
            default => ['qr', 'mission', 'treasure'],
        };

        if ($this->containsAny($name, ['عکس', 'دید', 'گنبد', 'موزه', 'اثر', 'ماکت'])) {
            $uses = array_values(array_unique([...$uses, 'qr', 'mission']));
        }

        return array_slice($uses, 0, 5);
    }

    private function priorityFor(string $name, string $function, string $confidence): string
    {
        if ($confidence === 'low') {
            return 'low';
        }

        if ($function === 'route' || $this->containsAny($name, ['اصلی', 'مرکزی', 'گنبد', 'سکوی دید', 'رستوران گردان', 'فودکورت', 'ورودی', 'بلیت', 'بلیط'])) {
            return 'primary';
        }

        return 'secondary';
    }

    /** @param array<int, string> $uses */
    private function notesFor(string $name, string $function, array $uses, string $confidence, bool $fieldReview): string
    {
        $useText = implode(',', $uses);
        $review = $fieldReview ? 'بله' : 'خیر';

        return "پیشنهاد نیمه‌مکانیزه برای {$name} با کارکرد {$function} و کاربرد {$useText}. سطح اطمینان: {$confidence}. نیازمند بازدید میدانی: {$review}.";
    }

    /** @return array<int, string> */
    private function facilityTriggers(): array
    {
        return [
            'کافه', 'رستوران', 'فود', 'فروشگاه', 'موزه', 'گنبد', 'سکو', 'دید', 'بلیت', 'بلیط',
            'پذیرش', 'ورودی', 'آسانسور', 'پارکینگ', 'باغ', 'پل', 'خانه', 'سالن', 'گذر', 'میدان',
            'تفریح', 'بازی', 'سوغات', 'صنایع دستی', 'نمایشگر', 'تابلو', 'ماکت', 'اثر هنری',
            'shop', 'cafe', 'restaurant', 'museum', 'ticket', 'gate',
        ];
    }

    /** @param array<int, string> $needles */
    private function containsAny(string $value, array $needles): bool
    {
        $value = mb_strtolower($value);

        foreach ($needles as $needle) {
            if (str_contains($value, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    private function profileValue(Venue $venue, string $key): ?string
    {
        $value = Arr::get(is_array($venue->metadata) ? $venue->metadata : [], "location_profile.{$key}");

        return filled($value) ? (string) $value : null;
    }

    /** @return array<int, array<string, mixed>> */
    private function profileFacilities(Venue $venue): array
    {
        $items = Arr::get(is_array($venue->metadata) ? $venue->metadata : [], 'location_profile.facilities', []);

        return is_array($items) ? array_values(array_filter($items, is_array(...))) : [];
    }

    private function key(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));
    }
}
