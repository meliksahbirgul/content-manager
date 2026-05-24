<?php

declare(strict_types=1);

namespace Source\Media\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('media')]
class Media extends Model
{
    protected $fillable = [
        'uuid',
        'mediable_type',
        'mediable_id',
        'collection',
        'disk',
        'path',
        'url',
        'original_name',
        'mime_type',
        'size',
        'alt_text',
        'link_page_uuid',
        'order',
    ];

    /** @return MorphTo<Model, $this> */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
