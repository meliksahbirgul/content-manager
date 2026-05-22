<?php

declare(strict_types=1);

namespace Source\Languages\Infrastructure\Persistence;

use Source\Languages\Domain\Entity\LanguageEntity;
use Source\Languages\Domain\Models\Language;
use Source\Languages\Domain\Repository\LanguageRepository;

class EloquentLanguageRepository implements LanguageRepository
{
    /** @return list<LanguageEntity> */
    public function all(): array
    {
        return array_values(
            Language::all()
                ->map(fn(Language $l) => $this->mapToEntity($l))
                ->all()
        );
    }

    private function mapToEntity(Language $language): LanguageEntity
    {
        return new LanguageEntity(
            uuid: $language->uuid,
            name: $language->name,
            code: $language->code,
            status: $language->status,
        );
    }
}
