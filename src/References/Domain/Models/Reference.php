<?php

declare(strict_types=1);

namespace Source\References\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Models\Media;

#[Table('references')]
#[Fillable(['uuid', 'name', 'order'])]
class Reference extends Model
{
    use SoftDeletes;

    /** @return MorphMany<Media,$this> */
    public function images(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')
            ->where('collection', MediaCollection::Images->value)
            ->orderBy('order');
    }
}
