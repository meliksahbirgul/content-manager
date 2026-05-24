<?php

declare(strict_types=1);

namespace Source\Roles\Domain\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Ramsey\Uuid\Uuid;
use Source\Users\Domain\Models\User;

#[Table('roles')]
#[Fillable(['uuid', 'name', 'display_name', 'description'])]
class Role extends Model
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $role): void {
            if (empty($role->uuid)) {
                $role->uuid = Uuid::uuid7()->toString();
            }
        });
    }

    /** @return BelongsToMany<Permission, $this> */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role');
    }
}
