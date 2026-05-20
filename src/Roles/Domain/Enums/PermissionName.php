<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Enums;

enum PermissionName: string
{
    case PagesView   = 'pages.view';
    case PagesCreate = 'pages.create';
    case PagesUpdate = 'pages.update';
    case PagesDelete = 'pages.delete';
}
