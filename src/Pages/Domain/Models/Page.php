<?php

declare(strict_types=1);

namespace Source\Pages\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Touches;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Models\Media;

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

    /** @return MorphMany<Media,$this> */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }

    /** @return MorphMany<Media,$this> */
    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')
            ->where('collection', MediaCollection::Images->value)
            ->orderBy('order');
    }

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
