<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsentVersion extends Model
{
    use HasUuids;

    protected $fillable = [
        'version', 'language', 'title', 'body', 'is_active', 'is_demo', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_demo' => 'boolean',
            'published_at' => 'immutable_datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ConsentLog::class);
    }
}
