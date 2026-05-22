<?php

declare(strict_types=1);

namespace Source\Languages\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

#[Table('languages')]
#[Fillable(['uuid', 'name', 'code', 'status'])]
class Language extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $language): void {
            if (empty($language->uuid)) {
                $language->uuid = Uuid::uuid7()->toString();
            }
        });
    }
}
