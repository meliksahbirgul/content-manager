<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Enums;

enum RoleName: string
{
    case Admin  = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';
}
