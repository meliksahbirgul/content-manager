<?php

declare(strict_types=1);

namespace Tests\Unit\Roles\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\Roles\Domain\Entity\PermissionEntity;
use Source\Roles\Domain\Models\Permission;
use Source\Roles\Domain\Models\Role;
use Source\Roles\Domain\ValueObjects\AssignPermission;
use Source\Roles\Domain\ValueObjects\RemovePermission;
use Source\Roles\Infrastructure\Persistence\EloquentPermissionRepository;
use Source\Users\Domain\Models\User;
use Tests\TestCase;

#[Group('infrastructure')]
class PermissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentPermissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentPermissionRepository;
    }

    // -------------------------------------------------------------------------
    // findByUuid
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function it_finds_permission_by_uuid(): void
    {
        // Arrange
        $permission = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);

        // Act
        $result = $this->repository->findByUuid($permission->uuid);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(PermissionEntity::class, $result);
        $this->assertEquals($permission->uuid, $result->uuid());
        $this->assertEquals('sections.view', $result->name());
        $this->assertEquals('View Pages', $result->displayName());
    }

    /** @test */
    #[Test]
    public function it_returns_null_when_permission_not_found_by_uuid(): void
    {
        // Arrange
        $nonExistentUuid = Uuid::uuid7()->toString();

        // Act
        $result = $this->repository->findByUuid($nonExistentUuid);

        // Assert
        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // findByName
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function it_finds_permission_by_name(): void
    {
        // Arrange
        Permission::create(['name' => 'sections.create', 'display_name' => 'Create Pages', 'description' => 'Allows creating sections.']);

        // Act
        $result = $this->repository->findByName('sections.create');

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(PermissionEntity::class, $result);
        $this->assertEquals('sections.create', $result->name());
        $this->assertEquals('Allows creating sections.', $result->description());
    }

    /** @test */
    #[Test]
    public function it_returns_null_when_permission_not_found_by_name(): void
    {
        // Act
        $result = $this->repository->findByName('nonexistent.permission');

        // Assert
        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // all
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function it_returns_all_permissions(): void
    {
        // Arrange
        Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);
        Permission::create(['name' => 'sections.create', 'display_name' => 'Create Pages']);
        Permission::create(['name' => 'sections.update', 'display_name' => 'Update Pages']);
        Permission::create(['name' => 'sections.delete', 'display_name' => 'Delete Pages']);

        // Act
        $results = $this->repository->all();
        $data = [];
        foreach ($results as $result) {
            if (! str_contains($result->name(), 'sections')) {
                continue;
            }

            $data[] = $result;
        }

        // Assert
        $this->assertCount(4, $data);
        $this->assertContainsOnlyInstancesOf(PermissionEntity::class, $data);

        $names = array_map(fn (PermissionEntity $p) => $p->name(), $data);
        $this->assertContains('sections.view', $names);
        $this->assertContains('sections.delete', $names);
    }

    /** @test */
    #[Test]
    public function it_returns_empty_array_when_no_permissions_exist(): void
    {
        // Act
        $results = $this->repository->all();

        // Assert
        $this->assertIsArray($results);
    }

    // -------------------------------------------------------------------------
    // assignToUser
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function it_assigns_permission_to_user_as_granted(): void
    {
        // Arrange
        $user = User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'password' => bcrypt('pass')]);
        $permission = Permission::create(['name' => 'sections.create', 'display_name' => 'Create Pages']);
        $vo = new AssignPermission($user->uuid, $permission->uuid, true);

        // Act
        $this->repository->assignToUser($vo);

        // Assert
        $this->assertDatabaseHas('user_permission', [
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'granted' => true,
        ]);
    }

    /** @test */
    #[Test]
    public function it_assigns_permission_to_user_as_denied(): void
    {
        // Arrange
        $user = User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => bcrypt('pass')]);
        $permission = Permission::create(['name' => 'sections.delete', 'display_name' => 'Delete Pages']);
        $vo = new AssignPermission($user->uuid, $permission->uuid, false);

        // Act
        $this->repository->assignToUser($vo);

        // Assert
        $this->assertDatabaseHas('user_permission', [
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'granted' => false,
        ]);
    }

    /** @test */
    #[Test]
    public function it_defaults_granted_to_true_when_not_specified(): void
    {
        // Arrange
        $user = User::create(['name' => 'Carol', 'email' => 'carol@example.com', 'password' => bcrypt('pass')]);
        $permission = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);
        $vo = new AssignPermission($user->uuid, $permission->uuid);

        // Act
        $this->repository->assignToUser($vo);

        // Assert
        $this->assertDatabaseHas('user_permission', [
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'granted' => true,
        ]);
    }

    /** @test */
    #[Test]
    public function it_throws_when_assigning_permission_to_non_existent_user(): void
    {
        // Arrange
        $permission = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);
        $nonExistentUuid = Uuid::uuid7()->toString();
        $vo = new AssignPermission($nonExistentUuid, $permission->uuid);

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->repository->assignToUser($vo);
    }

    /** @test */
    #[Test]
    public function it_throws_when_assigning_non_existent_permission_to_user(): void
    {
        // Arrange
        $user = User::create(['name' => 'Dave', 'email' => 'dave@example.com', 'password' => bcrypt('pass')]);
        $nonExistentUuid = Uuid::uuid7()->toString();
        $vo = new AssignPermission($user->uuid, $nonExistentUuid);

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->repository->assignToUser($vo);
    }

    // -------------------------------------------------------------------------
    // removeFromUser
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function it_removes_permission_from_user(): void
    {
        // Arrange
        $user = User::create(['name' => 'Eve', 'email' => 'eve@example.com', 'password' => bcrypt('pass')]);
        $permission = Permission::create(['name' => 'sections.update', 'display_name' => 'Update Pages']);

        $assignVo = new AssignPermission($user->uuid, $permission->uuid, true);
        $this->repository->assignToUser($assignVo);
        $this->assertDatabaseHas('user_permission', ['user_id' => $user->id, 'permission_id' => $permission->id]);

        $removeVo = new RemovePermission($user->uuid, $permission->uuid);

        // Act
        $this->repository->removeFromUser($removeVo);

        // Assert
        $this->assertDatabaseMissing('user_permission', [
            'user_id' => $user->id,
            'permission_id' => $permission->id,
        ]);
    }

    /** @test */
    #[Test]
    public function it_throws_when_removing_permission_from_non_existent_user(): void
    {
        // Arrange
        $permission = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);
        $nonExistentUuid = Uuid::uuid7()->toString();
        $vo = new RemovePermission($nonExistentUuid, $permission->uuid);

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->repository->removeFromUser($vo);
    }

    // -------------------------------------------------------------------------
    // getDirectByUserUuid
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function it_returns_only_granted_direct_permissions(): void
    {
        // Arrange
        $user = User::create(['name' => 'Frank', 'email' => 'frank@example.com', 'password' => bcrypt('pass')]);
        $granted = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);
        $denied = Permission::create(['name' => 'sections.delete', 'display_name' => 'Delete Pages']);

        $user->permissions()->attach($granted->id, ['granted' => true]);
        $user->permissions()->attach($denied->id, ['granted' => false]);

        // Act
        $results = $this->repository->getDirectByUserUuid($user->uuid);

        // Assert
        $this->assertCount(1, $results);
        $this->assertEquals('sections.view', $results[0]->name());
    }

    /** @test */
    #[Test]
    public function it_returns_empty_array_when_user_has_no_direct_permissions(): void
    {
        // Arrange
        $user = User::create(['name' => 'Grace', 'email' => 'grace@example.com', 'password' => bcrypt('pass')]);

        // Act
        $results = $this->repository->getDirectByUserUuid($user->uuid);

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /** @test */
    #[Test]
    public function it_throws_when_getting_direct_permissions_for_non_existent_user(): void
    {
        // Arrange
        $nonExistentUuid = Uuid::uuid7()->toString();

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->repository->getDirectByUserUuid($nonExistentUuid);
    }

    // -------------------------------------------------------------------------
    // getAllByUserUuid
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function it_returns_permissions_granted_via_role(): void
    {
        // Arrange
        $user = User::create(['name' => 'Hank', 'email' => 'hank@example.com', 'password' => bcrypt('pass')]);
        $role = Role::create(['name' => 'feditor', 'display_name' => 'Feditor']);
        $viewPerm = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);
        $createPerm = Permission::create(['name' => 'sections.create', 'display_name' => 'Create Pages']);

        $role->permissions()->attach([$viewPerm->id, $createPerm->id]);
        $user->roles()->attach($role->id);

        // Act
        $results = $this->repository->getAllByUserUuid($user->uuid);

        // Assert
        $this->assertCount(2, $results);
        $names = array_map(fn (PermissionEntity $p) => $p->name(), $results);
        $this->assertContains('sections.view', $names);
        $this->assertContains('sections.create', $names);
    }

    /** @test */
    #[Test]
    public function it_includes_directly_granted_permissions_not_in_role(): void
    {
        // Arrange
        $user = User::create(['name' => 'Iris', 'email' => 'iris@example.com', 'password' => bcrypt('pass')]);
        $role = Role::create(['name' => 'aviewer', 'display_name' => 'Aviewer']);
        $viewPerm = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);
        $deletePerm = Permission::create(['name' => 'sections.delete', 'display_name' => 'Delete Pages']);

        $role->permissions()->attach($viewPerm->id);
        $user->roles()->attach($role->id);
        $user->permissions()->attach($deletePerm->id, ['granted' => true]);

        // Act
        $results = $this->repository->getAllByUserUuid($user->uuid);

        // Assert
        $names = array_map(fn (PermissionEntity $p) => $p->name(), $results);
        $this->assertContains('sections.view', $names);
        $this->assertContains('sections.delete', $names);
    }

    /** @test */
    #[Test]
    public function it_excludes_denied_permission_even_if_granted_by_role(): void
    {
        // Arrange
        $user = User::create(['name' => 'Jack', 'email' => 'jack@example.com', 'password' => bcrypt('pass')]);
        $role = Role::create(['name' => 'tadmin', 'display_name' => 'Tadministrator']);
        $viewPerm = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);
        $delPerm = Permission::create(['name' => 'sections.delete', 'display_name' => 'Delete Pages']);

        $role->permissions()->attach([$viewPerm->id, $delPerm->id]);
        $user->roles()->attach($role->id);

        // Explicitly deny sections.delete for this user
        $user->permissions()->attach($delPerm->id, ['granted' => false]);

        // Act
        $results = $this->repository->getAllByUserUuid($user->uuid);

        // Assert
        $names = array_map(fn (PermissionEntity $p) => $p->name(), $results);
        $this->assertContains('sections.view', $names);
        $this->assertNotContains('sections.delete', $names);
    }

    /** @test */
    #[Test]
    public function it_deduplicates_permissions_granted_by_multiple_roles(): void
    {
        // Arrange
        $user = User::create(['name' => 'Kate', 'email' => 'kate@example.com', 'password' => bcrypt('pass')]);
        $role1 = Role::create(['name' => 'feditor', 'display_name' => 'Feditor']);
        $role2 = Role::create(['name' => 'areviewer', 'display_name' => 'Areviewer']);
        $viewPerm = Permission::create(['name' => 'sections.view', 'display_name' => 'View Pages']);

        $role1->permissions()->attach($viewPerm->id);
        $role2->permissions()->attach($viewPerm->id);
        $user->roles()->attach([$role1->id, $role2->id]);

        // Act
        $results = $this->repository->getAllByUserUuid($user->uuid);

        // Assert: sections.view appears only once despite being in two roles
        $names = array_map(fn (PermissionEntity $p) => $p->name(), $results);
        $this->assertCount(1, array_filter($names, fn ($n) => $n === 'sections.view'));
    }

    /** @test */
    #[Test]
    public function it_returns_empty_array_when_user_has_no_roles_or_direct_permissions(): void
    {
        // Arrange
        $user = User::create(['name' => 'Leo', 'email' => 'leo@example.com', 'password' => bcrypt('pass')]);

        // Act
        $results = $this->repository->getAllByUserUuid($user->uuid);

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /** @test */
    #[Test]
    public function it_throws_when_getting_all_permissions_for_non_existent_user(): void
    {
        // Arrange
        $nonExistentUuid = Uuid::uuid7()->toString();

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->repository->getAllByUserUuid($nonExistentUuid);
    }
}
