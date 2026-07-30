<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $audience_type
 * @property string|null $organization_name
 * @property string $contact_name
 * @property string $mobile
 * @property string|null $city
 * @property string|null $project_hint
 * @property string|null $notes
 * @property string $status
 * @property string|null $source_path
 * @property array<string, mixed>|null $metadata
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
class MarketingLead extends Model
{
    use HasUuids;

    protected $fillable = [
        'audience_type',
        'organization_name',
        'contact_name',
        'mobile',
        'city',
        'project_hint',
        'notes',
        'status',
        'source_path',
        'metadata',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
