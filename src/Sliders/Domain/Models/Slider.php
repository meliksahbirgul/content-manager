<?php

declare(strict_types=1);

namespace Source\Sliders\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Models\Media;

#[Table('sliders')]
#[Fillable(['uuid', 'title', 'href', 'order', 'is_active'])]
class Slider extends Model
{
    use SoftDeletes;

    protected $casts = [
        'title'     => 'array',
        'href'      => 'array',
        'is_active' => 'string',
    ];

    /** @return MorphMany<Media,$this> */
    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')
            ->where('collection', MediaCollection::Images->value)
            ->orderBy('order');
    }
}
