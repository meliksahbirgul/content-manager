<?php

declare(strict_types=1);

namespace Source\Media\Domain\Enums;

enum MediaDisk: string
{
    case Public = 'public';
    case S3 = 's3';
}
