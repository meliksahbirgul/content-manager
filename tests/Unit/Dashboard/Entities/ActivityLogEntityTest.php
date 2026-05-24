<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Entities;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Dashboard\Domain\Entity\ActivityLogEntity;

class ActivityLogEntityTest extends TestCase
{
    private DateTimeImmutable $createdAt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createdAt = new DateTimeImmutable('2024-01-15 10:30:00');
    }

    /** @test */
    #[Test]
    public function should_create_instance_with_all_parameters(): void
    {
        // GIVEN: All parameters
        $id = 1;
        $logName = 'default';
        $description = 'Page created';
        $event = 'created';
        $properties = ['title' => 'My Page', 'status' => 'active'];
        $causerId = 42;

        // WHEN: Creating ActivityLogEntity
        $entity = new ActivityLogEntity(
            id: $id,
            logName: $logName,
            description: $description,
            event: $event,
            properties: $properties,
            causerId: $causerId,
            createdAt: $this->createdAt,
        );

        // THEN: Should create instance with correct data
        $this->assertInstanceOf(ActivityLogEntity::class, $entity);
        $this->assertEquals($id, $entity->id());
        $this->assertEquals($logName, $entity->logName());
        $this->assertEquals($description, $entity->description());
        $this->assertEquals($event, $entity->event());
        $this->assertEquals($properties, $entity->properties());
        $this->assertEquals($causerId, $entity->causerId());
        $this->assertEquals($this->createdAt, $entity->createdAt());
    }

    /** @test */
    #[Test]
    public function should_create_instance_with_nullable_fields_as_null(): void
    {
        // GIVEN: Nullable fields set to null
        $entity = new ActivityLogEntity(
            id: 5,
            logName: null,
            description: 'Something happened',
            event: null,
            properties: [],
            causerId: null,
            createdAt: $this->createdAt,
        );

        // THEN: Nullable fields should be null
        $this->assertNull($entity->logName());
        $this->assertNull($entity->event());
        $this->assertNull($entity->causerId());
    }

    /** @test */
    #[Test]
    public function should_return_correct_id(): void
    {
        // GIVEN: ActivityLogEntity with a specific id
        $entity = new ActivityLogEntity(
            id: 99,
            logName: null,
            description: 'desc',
            event: null,
            properties: [],
            causerId: null,
            createdAt: $this->createdAt,
        );

        // WHEN: Calling id()
        $result = $entity->id();

        // THEN: Should return the correct integer id
        $this->assertSame(99, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    #[Test]
    public function should_return_correct_description(): void
    {
        // GIVEN: ActivityLogEntity with specific description
        $description = 'User updated a page title';
        $entity = new ActivityLogEntity(
            id: 1,
            logName: null,
            description: $description,
            event: null,
            properties: [],
            causerId: null,
            createdAt: $this->createdAt,
        );

        // WHEN: Calling description()
        $result = $entity->description();

        // THEN: Should return the exact description string
        $this->assertSame($description, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function should_return_correct_properties(): void
    {
        // GIVEN: Properties with nested data
        $properties = [
            'old' => ['title' => 'Old Title'],
            'new' => ['title' => 'New Title'],
            'count' => 5,
            'flag' => true,
        ];

        $entity = new ActivityLogEntity(
            id: 1,
            logName: 'audit',
            description: 'updated',
            event: 'updated',
            properties: $properties,
            causerId: 1,
            createdAt: $this->createdAt,
        );

        // WHEN: Calling properties()
        $result = $entity->properties();

        // THEN: Should return the exact properties array
        $this->assertEquals($properties, $result);
        $this->assertIsArray($result);
        $this->assertEquals('Old Title', $result['old']['title']);
        $this->assertEquals('New Title', $result['new']['title']);
    }

    /** @test */
    #[Test]
    public function should_return_empty_properties(): void
    {
        // GIVEN: ActivityLogEntity with no properties
        $entity = new ActivityLogEntity(
            id: 1,
            logName: null,
            description: 'logged',
            event: 'created',
            properties: [],
            causerId: null,
            createdAt: $this->createdAt,
        );

        // WHEN: Calling properties()
        $result = $entity->properties();

        // THEN: Should return empty array
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function should_return_correct_created_at(): void
    {
        // GIVEN: A specific DateTimeImmutable
        $createdAt = new DateTimeImmutable('2025-06-01 12:00:00');
        $entity = new ActivityLogEntity(
            id: 1,
            logName: null,
            description: 'desc',
            event: null,
            properties: [],
            causerId: null,
            createdAt: $createdAt,
        );

        // WHEN: Calling createdAt()
        $result = $entity->createdAt();

        // THEN: Should return the correct DateTimeImmutable
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertEquals($createdAt, $result);
        $this->assertSame('2025-06-01 12:00:00', $result->format('Y-m-d H:i:s'));
    }

    /** @test */
    #[Test]
    public function should_return_correct_types_for_all_properties(): void
    {
        // GIVEN: A fully populated ActivityLogEntity
        $entity = new ActivityLogEntity(
            id: 7,
            logName: 'system',
            description: 'something happened',
            event: 'deleted',
            properties: ['key' => 'value'],
            causerId: 10,
            createdAt: $this->createdAt,
        );

        // THEN: Each getter should return the correct PHP type
        $this->assertIsInt($entity->id());
        $this->assertIsString($entity->logName());
        $this->assertIsString($entity->description());
        $this->assertIsString($entity->event());
        $this->assertIsArray($entity->properties());
        $this->assertIsInt($entity->causerId());
        $this->assertInstanceOf(DateTimeImmutable::class, $entity->createdAt());
    }

    /** @test */
    #[Test]
    public function should_be_immutable_after_construction(): void
    {
        // GIVEN: An ActivityLogEntity instance
        $entity = new ActivityLogEntity(
            id: 1,
            logName: 'default',
            description: 'initial description',
            event: 'created',
            properties: ['a' => 1],
            causerId: 3,
            createdAt: $this->createdAt,
        );

        // WHEN: Accessing the same property twice
        $firstCall = $entity->description();
        $secondCall = $entity->description();

        // THEN: Both calls should return the same value (immutability)
        $this->assertSame($firstCall, $secondCall);
        $this->assertSame($entity->id(), $entity->id());
        $this->assertSame($entity->causerId(), $entity->causerId());
    }

    /** @test */
    #[Test]
    public function should_create_multiple_independent_instances(): void
    {
        // GIVEN: Two separate ActivityLogEntity instances
        $entity1 = new ActivityLogEntity(
            id: 1,
            logName: 'log-a',
            description: 'first event',
            event: 'created',
            properties: [],
            causerId: 10,
            createdAt: new DateTimeImmutable('2024-01-01'),
        );

        $entity2 = new ActivityLogEntity(
            id: 2,
            logName: 'log-b',
            description: 'second event',
            event: 'deleted',
            properties: ['removed' => true],
            causerId: null,
            createdAt: new DateTimeImmutable('2024-06-01'),
        );

        // THEN: Each instance should hold independent data
        $this->assertNotEquals($entity1->id(), $entity2->id());
        $this->assertNotEquals($entity1->description(), $entity2->description());
        $this->assertNotEquals($entity1->event(), $entity2->event());
        $this->assertNotNull($entity1->causerId());
        $this->assertNull($entity2->causerId());
    }
}
