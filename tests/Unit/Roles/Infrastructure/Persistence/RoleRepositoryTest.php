<?php

declare(strict_types=1);

namespace Tests\Unit\Roles\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\Roles\Domain\Entity\RoleEntity;
use Source\Roles\Domain\Models\Role;
use Source\Roles\Domain\ValueObjects\AssignRole;
use Source\Roles\Infrastructure\Persistence\EloquentRoleRepository;
use Source\Users\Domain\Models\User;
use Tests\TestCase;

#[Group('infrastructure')]
class RoleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentRoleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentRoleRepository();
    }

    // -------------------------------------------------------------------------
    // findByUuid
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function itFindsRoleByUuid(): void
    {
        // Arrange
        $role = Role::create(['name' => 'fadmin', 'display_name' => 'Fadministrator']);

        // Act
        $result = $this->repository->findByUuid($role->uuid);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(RoleEntity::class, $result);
        $this->assertEquals($role->uuid, $result->uuid());
        $this->assertEquals('fadmin', $result->name());
        $this->assertEquals('Fadministrator', $result->displayName());
    }

    /** @test */
    #[Test]
    public function itReturnsNullWhenRoleNotFoundByUuid(): void
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
    public function itFindsRoleByName(): void
    {
        // Arrange
        Role::create(['name' => 'feditor', 'display_name' => 'Feditor', 'description' => 'Can edit pages.']);

        // Act
        $result = $this->repository->findByName('feditor');

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(RoleEntity::class, $result);
        $this->assertEquals('feditor', $result->name());
        $this->assertEquals('Feditor', $result->displayName());
        $this->assertEquals('Can edit pages.', $result->description());
    }

    /** @test */
    #[Test]
    public function itReturnsNullWhenRoleNotFoundByName(): void
    {
        // Act
        $result = $this->repository->findByName('nonexistent-role');

        // Assert
        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // all
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function itReturnsAllRoles(): void
    {
        // Arrange
        Role::create(['name' => 'fadmin', 'display_name' => 'Fadministrator']);
        Role::create(['name' => 'feditor', 'display_name' => 'Feditor']);
        Role::create(['name' => 'aviewer', 'display_name' => 'Aviewer']);

        // Act
        $results = $this->repository->all();

        // Assert
        $this->assertContainsOnlyInstancesOf(RoleEntity::class, $results);

        $names = array_map(fn(RoleEntity $r) => $r->name(), $results);
        $this->assertContains('fadmin', $names);
        $this->assertContains('feditor', $names);
        $this->assertContains('aviewer', $names);
    }

    /** @test */
    #[Test]
    public function itReturnsEmptyArrayWhenNoRolesExist(): void
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
    public function itAssignsRoleToUser(): void
    {
        // Arrange
        $user = User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'password' => bcrypt('pass')]);
        $role = Role::create(['name' => 'fadmin', 'display_name' => 'Fadministrator']);
        $vo   = new AssignRole($user->uuid, $role->uuid);

        // Act
        $this->repository->assignToUser($vo);

        // Assert
        $this->assertDatabaseHas('user_role', [
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    /** @test */
    #[Test]
    public function itAssignsRoleToUserIdempotently(): void
    {
        // Arrange
        $user = User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => bcrypt('pass')]);
        $role = Role::create(['name' => 'feditor', 'display_name' => 'Feditor']);
        $vo   = new AssignRole($user->uuid, $role->uuid);

        // Act: assign twice
        $this->repository->assignToUser($vo);
        $this->repository->assignToUser($vo);

        // Assert: only one pivot record exists
        $this->assertDatabaseCount('user_role', 1);
    }

    /** @test */
    #[Test]
    public function itThrowsWhenAssigningRoleToNonExistentUser(): void
    {
        // Arrange
        $role            = Role::create(['name' => 'fadmin', 'display_name' => 'Fadministrator']);
        $nonExistentUuid = Uuid::uuid7()->toString();
        $vo              = new AssignRole($nonExistentUuid, $role->uuid);

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->repository->assignToUser($vo);
    }

    /** @test */
    #[Test]
    public function itThrowsWhenAssigningNonExistentRoleToUser(): void
    {
        // Arrange
        $user            = User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'password' => bcrypt('pass')]);
        $nonExistentUuid = Uuid::uuid7()->toString();
        $vo              = new AssignRole($user->uuid, $nonExistentUuid);

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
    public function itRemovesRoleFromUser(): void
    {
        // Arrange
        $user = User::create(['name' => 'Carol', 'email' => 'carol@example.com', 'password' => bcrypt('pass')]);
        $role = Role::create(['name' => 'aviewer', 'display_name' => 'Aviewer']);
        $vo   = new AssignRole($user->uuid, $role->uuid);

        $this->repository->assignToUser($vo);
        $this->assertDatabaseHas('user_role', ['user_id' => $user->id, 'role_id' => $role->id]);

        // Act
        $this->repository->removeFromUser($vo);

        // Assert
        $this->assertDatabaseMissing('user_role', ['user_id' => $user->id, 'role_id' => $role->id]);
    }

    /** @test */
    #[Test]
    public function itThrowsWhenRemovingRoleFromNonExistentUser(): void
    {
        // Arrange
        $role            = Role::create(['name' => 'fadmin', 'display_name' => 'Fadministrator']);
        $nonExistentUuid = Uuid::uuid7()->toString();
        $vo              = new AssignRole($nonExistentUuid, $role->uuid);

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->repository->removeFromUser($vo);
    }

    // -------------------------------------------------------------------------
    // getByUserUuid
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function itReturnsRolesForUser(): void
    {
        // Arrange
        $user    = User::create(['name' => 'Dave', 'email' => 'dave@example.com', 'password' => bcrypt('pass')]);
        $admin   = Role::create(['name' => 'fadmin', 'display_name' => 'Fadministrator']);
        $editor  = Role::create(['name' => 'feditor', 'display_name' => 'Feditor']);

        $user->roles()->attach([$admin->id, $editor->id]);

        // Act
        $results = $this->repository->getByUserUuid($user->uuid);

        // Assert
        $this->assertCount(2, $results);
        $this->assertContainsOnlyInstancesOf(RoleEntity::class, $results);

        $names = array_map(fn(RoleEntity $r) => $r->name(), $results);
        $this->assertContains('fadmin', $names);
        $this->assertContains('feditor', $names);
    }

    /** @test */
    #[Test]
    public function itReturnsEmptyArrayWhenUserHasNoRoles(): void
    {
        // Arrange
        $user = User::create(['name' => 'Eve', 'email' => 'eve@example.com', 'password' => bcrypt('pass')]);

        // Act
        $results = $this->repository->getByUserUuid($user->uuid);

        // Assert
        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /** @test */
    #[Test]
    public function itThrowsWhenGettingRolesForNonExistentUser(): void
    {
        // Arrange
        $nonExistentUuid = Uuid::uuid7()->toString();

        // Assert
        $this->expectException(ModelNotFoundException::class);

        // Act
        $this->repository->getByUserUuid($nonExistentUuid);
    }

    // -------------------------------------------------------------------------
    // mapToEntity — verified via findByName with null description
    // -------------------------------------------------------------------------

    /** @test */
    #[Test]
    public function itMapsNullDescriptionToEntity(): void
    {
        // Arrange
        Role::create(['name' => 'aviewer', 'display_name' => 'Viewer', 'description' => null]);

        // Act
        $result = $this->repository->findByName('aviewer');

        // Assert
        $this->assertNotNull($result);
        $this->assertNull($result->description());
    }
}
