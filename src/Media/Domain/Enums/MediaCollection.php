<?php

declare(strict_types=1);

namespace Source\Media\Domain\Enums;

enum MediaCollection: string
{
    case Images = 'images';
    case Avatar = 'avatar';
    case Thumbnail = 'thumbnail';
    case Default = 'default';
}
