<?php

declare(strict_types=1);

namespace Tests\Unit\Roles\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Roles\Domain\Entity\RoleEntity;

class RoleEntityTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithAllFields(): void
    {
        // GIVEN: Valid role data
        $uuid        = Uuid::uuid7()->toString();
        $name        = 'admin';
        $displayName = 'Administrator';
        $description = 'Full access to all resources.';

        // WHEN: Creating RoleEntity
        $role = new RoleEntity($uuid, $name, $displayName, $description);

        // THEN: All getters return the provided values
        $this->assertInstanceOf(RoleEntity::class, $role);
        $this->assertEquals($uuid, $role->uuid());
        $this->assertEquals($name, $role->name());
        $this->assertEquals($displayName, $role->displayName());
        $this->assertEquals($description, $role->description());
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithNullDescription(): void
    {
        // GIVEN: Role data without description
        $uuid        = Uuid::uuid7()->toString();
        $name        = 'viewer';
        $displayName = 'Viewer';

        // WHEN: Creating RoleEntity with null description
        $role = new RoleEntity($uuid, $name, $displayName, null);

        // THEN: Description should be null
        $this->assertNull($role->description());
        $this->assertEquals($name, $role->name());
    }

    /** @test */
    #[Test]
    public function shouldReturnUuidCorrectly(): void
    {
        // GIVEN: A specific uuid
        $uuid = Uuid::uuid7()->toString();
        $role = new RoleEntity($uuid, 'editor', 'Editor', null);

        // WHEN: Calling uuid()
        $result = $role->uuid();

        // THEN: Should return the exact uuid
        $this->assertEquals($uuid, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnNameCorrectly(): void
    {
        // GIVEN: A specific name slug
        $name = 'editor';
        $role = new RoleEntity(Uuid::uuid7()->toString(), $name, 'Editor', null);

        // WHEN: Calling name()
        $result = $role->name();

        // THEN: Should return the exact name
        $this->assertEquals($name, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnDisplayNameCorrectly(): void
    {
        // GIVEN: A specific display name
        $displayName = 'Content Editor';
        $role        = new RoleEntity(Uuid::uuid7()->toString(), 'editor', $displayName, null);

        // WHEN: Calling displayName()
        $result = $role->displayName();

        // THEN: Should return the exact display name
        $this->assertEquals($displayName, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnDescriptionCorrectly(): void
    {
        // GIVEN: A specific description
        $description = 'Can create and edit pages.';
        $role        = new RoleEntity(Uuid::uuid7()->toString(), 'editor', 'Editor', $description);

        // WHEN: Calling description()
        $result = $role->description();

        // THEN: Should return the exact description
        $this->assertEquals($description, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyStringName(): void
    {
        // GIVEN: Role with empty name
        $role = new RoleEntity(Uuid::uuid7()->toString(), '', 'Display', null);

        // WHEN: Calling name()
        $result = $role->name();

        // THEN: Should preserve empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyStringDisplayName(): void
    {
        // GIVEN: Role with empty display name
        $role = new RoleEntity(Uuid::uuid7()->toString(), 'slug', '', null);

        // WHEN: Calling displayName()
        $result = $role->displayName();

        // THEN: Should preserve empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleUnicodeInDisplayName(): void
    {
        // GIVEN: Display name with unicode characters
        $displayName = 'Yönetici';
        $role        = new RoleEntity(Uuid::uuid7()->toString(), 'admin', $displayName, null);

        // WHEN: Calling displayName()
        $result = $role->displayName();

        // THEN: Should preserve unicode characters
        $this->assertEquals($displayName, $result);
        $this->assertStringContainsString('ö', $result);
    }

    /** @test */
    #[Test]
    public function shouldMaintainImmutabilityAcrossMultipleCalls(): void
    {
        // GIVEN: A RoleEntity instance
        $role = new RoleEntity(Uuid::uuid7()->toString(), 'admin', 'Administrator', 'Full access.');

        // WHEN: Calling each getter multiple times
        $name1 = $role->name();
        $name2 = $role->name();
        $desc1 = $role->description();
        $desc2 = $role->description();

        // THEN: Values should be identical across calls
        $this->assertSame($name1, $name2);
        $this->assertSame($desc1, $desc2);
    }

    /** @test */
    #[Test]
    public function shouldNotShareStateBetweenInstances(): void
    {
        // GIVEN: Two separate RoleEntity instances
        $role1 = new RoleEntity(Uuid::uuid7()->toString(), 'admin', 'Administrator', null);
        $role2 = new RoleEntity(Uuid::uuid7()->toString(), 'viewer', 'Viewer', null);

        // WHEN: Accessing names from both
        $name1 = $role1->name();
        $name2 = $role2->name();

        // THEN: Each instance holds its own state
        $this->assertEquals('admin', $name1);
        $this->assertEquals('viewer', $name2);
        $this->assertNotEquals($name1, $name2);
    }

    /** @test */
    #[Test]
    public function shouldPreserveExactCasingInName(): void
    {
        // GIVEN: Name with mixed casing (unusual but entity should not alter it)
        $name = 'Super_Admin_V2';
        $role = new RoleEntity(Uuid::uuid7()->toString(), $name, 'Display', null);

        // WHEN: Calling name()
        $result = $role->name();

        // THEN: Exact casing preserved
        $this->assertEquals($name, $result);
    }
}
