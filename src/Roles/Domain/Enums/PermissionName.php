<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Enums;

enum PermissionName: string
{
    case PagesView = 'pages.view';
    case PagesCreate = 'pages.create';
    case PagesUpdate = 'pages.update';
    case PagesDelete = 'pages.delete';

    case SlidersView = 'sliders.view';
    case SlidersCreate = 'sliders.create';
    case SlidersUpdate = 'sliders.update';
    case SlidersDelete = 'sliders.delete';

    case ReferencesView = 'references.view';
    case ReferencesCreate = 'references.create';
    case ReferencesUpdate = 'references.update';
    case ReferencesDelete = 'references.delete';
}
