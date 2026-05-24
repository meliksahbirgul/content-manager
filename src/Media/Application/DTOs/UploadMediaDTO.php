<?php

declare(strict_types=1);

namespace Source\Media\Application\DTOs;

use Illuminate\Http\UploadedFile;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;

readonly class UploadMediaDTO
{
    public function __construct(
        public string $mediableType,
        public int $mediableId,
        public UploadedFile $file,
        public MediaCollection $collection,
        public MediaDisk $disk,
        public ?string $altText = null,
        public ?string $linkPageUuid = null,
        public int $order = 0,
    ) {}
}
