<?php

declare(strict_types=1);

namespace Tests\Unit\Roles\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Roles\Domain\Entity\PermissionEntity;

class PermissionEntityTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithAllFields(): void
    {
        // GIVEN: Valid permission data
        $uuid        = Uuid::uuid7()->toString();
        $name        = 'pages.create';
        $displayName = 'Create Pages';
        $description = 'Allows creating new pages.';

        // WHEN: Creating PermissionEntity
        $permission = new PermissionEntity($uuid, $name, $displayName, $description);

        // THEN: All getters return the provided values
        $this->assertInstanceOf(PermissionEntity::class, $permission);
        $this->assertEquals($uuid, $permission->uuid());
        $this->assertEquals($name, $permission->name());
        $this->assertEquals($displayName, $permission->displayName());
        $this->assertEquals($description, $permission->description());
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithNullDescription(): void
    {
        // GIVEN: Permission data without description
        $uuid        = Uuid::uuid7()->toString();
        $name        = 'pages.view';
        $displayName = 'View Pages';

        // WHEN: Creating PermissionEntity with null description
        $permission = new PermissionEntity($uuid, $name, $displayName, null);

        // THEN: Description should be null, other fields intact
        $this->assertNull($permission->description());
        $this->assertEquals($name, $permission->name());
    }

    /** @test */
    #[Test]
    public function shouldReturnUuidCorrectly(): void
    {
        // GIVEN: A specific uuid
        $uuid       = Uuid::uuid7()->toString();
        $permission = new PermissionEntity($uuid, 'pages.view', 'View Pages', null);

        // WHEN: Calling uuid()
        $result = $permission->uuid();

        // THEN: Should return the exact uuid
        $this->assertEquals($uuid, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnNameWithDotNotationCorrectly(): void
    {
        // GIVEN: A permission name using resource.action format
        $name       = 'pages.delete';
        $permission = new PermissionEntity(Uuid::uuid7()->toString(), $name, 'Delete Pages', null);

        // WHEN: Calling name()
        $result = $permission->name();

        // THEN: Should preserve dot-notation format
        $this->assertEquals($name, $result);
        $this->assertStringContainsString('.', $result);
    }

    /** @test */
    #[Test]
    public function shouldReturnDisplayNameCorrectly(): void
    {
        // GIVEN: A specific display name
        $displayName = 'Update Pages';
        $permission  = new PermissionEntity(Uuid::uuid7()->toString(), 'pages.update', $displayName, null);

        // WHEN: Calling displayName()
        $result = $permission->displayName();

        // THEN: Should return the exact display name
        $this->assertEquals($displayName, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnDescriptionCorrectly(): void
    {
        // GIVEN: A specific description
        $description = 'Allows updating existing pages.';
        $permission  = new PermissionEntity(Uuid::uuid7()->toString(), 'pages.update', 'Update Pages', $description);

        // WHEN: Calling description()
        $result = $permission->description();

        // THEN: Should return the exact description
        $this->assertEquals($description, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyStringName(): void
    {
        // GIVEN: Permission with empty name
        $permission = new PermissionEntity(Uuid::uuid7()->toString(), '', 'Display', null);

        // WHEN: Calling name()
        $result = $permission->name();

        // THEN: Should preserve empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyStringDisplayName(): void
    {
        // GIVEN: Permission with empty display name
        $permission = new PermissionEntity(Uuid::uuid7()->toString(), 'pages.view', '', null);

        // WHEN: Calling displayName()
        $result = $permission->displayName();

        // THEN: Should preserve empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleUnicodeInDescription(): void
    {
        // GIVEN: Description with unicode characters
        $description = 'Sayfaları görüntüleme izni.';
        $permission  = new PermissionEntity(Uuid::uuid7()->toString(), 'pages.view', 'View Pages', $description);

        // WHEN: Calling description()
        $result = $permission->description();

        // THEN: Should preserve unicode characters
        $this->assertEquals($description, $result);
        $this->assertStringContainsString('ü', $result);
    }

    /** @test */
    #[Test]
    public function shouldMaintainImmutabilityAcrossMultipleCalls(): void
    {
        // GIVEN: A PermissionEntity instance
        $permission = new PermissionEntity(Uuid::uuid7()->toString(), 'pages.create', 'Create Pages', 'Desc.');

        // WHEN: Calling each getter multiple times
        $name1 = $permission->name();
        $name2 = $permission->name();
        $uuid1 = $permission->uuid();
        $uuid2 = $permission->uuid();

        // THEN: Values should be identical across calls
        $this->assertSame($name1, $name2);
        $this->assertSame($uuid1, $uuid2);
    }

    /** @test */
    #[Test]
    public function shouldNotShareStateBetweenInstances(): void
    {
        // GIVEN: Two separate PermissionEntity instances
        $perm1 = new PermissionEntity(Uuid::uuid7()->toString(), 'pages.create', 'Create Pages', null);
        $perm2 = new PermissionEntity(Uuid::uuid7()->toString(), 'pages.delete', 'Delete Pages', null);

        // WHEN: Accessing names from both
        $name1 = $perm1->name();
        $name2 = $perm2->name();

        // THEN: Each instance holds its own state
        $this->assertEquals('pages.create', $name1);
        $this->assertEquals('pages.delete', $name2);
        $this->assertNotEquals($name1, $name2);
    }

    /** @test */
    #[Test]
    public function shouldPreserveExactDotNotationName(): void
    {
        // GIVEN: All four standard page permission names
        $names = ['pages.view', 'pages.create', 'pages.update', 'pages.delete'];

        foreach ($names as $name) {
            // WHEN: Creating a PermissionEntity
            $permission = new PermissionEntity(Uuid::uuid7()->toString(), $name, 'Display', null);

            // THEN: Name is preserved exactly
            $this->assertEquals($name, $permission->name());
        }
    }
}
