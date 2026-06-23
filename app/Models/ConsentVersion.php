<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $version
 * @property string $language
 * @property string $title
 * @property string $body
 * @property bool $is_active
 * @property bool $is_demo
 * @property CarbonImmutable|null $published_at
 */
class ConsentVersion extends Model
{
    use HasUuids;

    protected $fillable = [
        'version', 'language', 'title', 'body', 'is_active', 'is_demo', 'published_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
            'published_at' => 'immutable_datetime',
        ];
    }

    /** @return HasMany<ConsentLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(ConsentLog::class);
    }
}
