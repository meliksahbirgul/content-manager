<?php

declare(strict_types=1);

namespace Source\Languages\Domain\Repository;

use Source\Languages\Domain\Entity\LanguageEntity;

interface LanguageRepository
{
    /** @return list<LanguageEntity> */
    public function all(): array;
}
