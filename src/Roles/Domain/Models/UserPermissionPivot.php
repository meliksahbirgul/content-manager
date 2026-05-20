<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserPermissionPivot extends Pivot
{
    public bool $granted;
}
