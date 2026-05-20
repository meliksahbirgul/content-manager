<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ramsey\Uuid\Uuid;
use Source\Users\Domain\Models\User;

#[Table('permissions')]
#[Fillable(['uuid', 'name', 'display_name', 'description'])]
class Permission extends Model
{

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $permission): void {
            if (empty($permission->uuid)) {
                $permission->uuid = Uuid::uuid7()->toString();
            }
        });
    }

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /** @return BelongsToMany<User, $this, UserPermissionPivot> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permission')
            ->using(UserPermissionPivot::class)
            ->withPivot('granted');
    }
}
