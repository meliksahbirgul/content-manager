<?php

declare(strict_types=1);

namespace Source\Pages\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Touches;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('pages')]
#[Fillable(['uuid', 'parent_id', 'title', 'content', 'slug', 'order', 'is_active'])]
#[Touches(['parentPage'])]
class Page extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'content' => 'array',
            'slug' => 'array',
            'is_active' => 'string',
            'created_at' => 'datetime',
        ];
    }

    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'slug' => 'array',
        'metadata' => 'array',
        'is_active' => 'string',
    ];

    /** @return HasMany<Page,$this> */
    public function subPages(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('order');
    }

    /** @return BelongsTo<Page,$this> */
    public function parentPage(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id', 'id');
    }
}
