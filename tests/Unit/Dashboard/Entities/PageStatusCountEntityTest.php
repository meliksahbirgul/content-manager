<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Dashboard\Domain\Entity\PageStatusCountEntity;

class PageStatusCountEntityTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithRequiredParameters(): void
    {
        // GIVEN: Valid status and count
        $status = 'active';
        $count  = 10;

        // WHEN: Creating PageStatusCountEntity
        $entity = new PageStatusCountEntity(status: $status, count: $count);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(PageStatusCountEntity::class, $entity);
        $this->assertEquals($status, $entity->status());
        $this->assertEquals($count, $entity->count());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectStatus(): void
    {
        // GIVEN: A PageStatusCountEntity with status "passive"
        $entity = new PageStatusCountEntity(status: 'passive', count: 3);

        // WHEN: Calling status()
        $result = $entity->status();

        // THEN: Should return the exact status string
        $this->assertSame('passive', $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectCount(): void
    {
        // GIVEN: A PageStatusCountEntity with count 42
        $entity = new PageStatusCountEntity(status: 'active', count: 42);

        // WHEN: Calling count()
        $result = $entity->count();

        // THEN: Should return the correct integer count
        $this->assertSame(42, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleZeroCount(): void
    {
        // GIVEN: A status with zero pages
        $entity = new PageStatusCountEntity(status: 'draft', count: 0);

        // WHEN: Calling count()
        $result = $entity->count();

        // THEN: Should return zero
        $this->assertSame(0, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleLargeCount(): void
    {
        // GIVEN: A status with a very large number of pages
        $entity = new PageStatusCountEntity(status: 'active', count: PHP_INT_MAX);

        // THEN: Should handle the large integer correctly
        $this->assertSame(PHP_INT_MAX, $entity->count());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectTypesForAllProperties(): void
    {
        // GIVEN: A PageStatusCountEntity instance
        $entity = new PageStatusCountEntity(status: 'active', count: 5);

        // THEN: Getters should return the expected PHP types
        $this->assertIsString($entity->status());
        $this->assertIsInt($entity->count());
    }

    /** @test */
    #[Test]
    public function shouldBeImmutableAfterConstruction(): void
    {
        // GIVEN: A PageStatusCountEntity instance
        $entity = new PageStatusCountEntity(status: 'active', count: 7);

        // WHEN: Accessing properties multiple times
        $firstStatus  = $entity->status();
        $secondStatus = $entity->status();
        $firstCount   = $entity->count();
        $secondCount  = $entity->count();

        // THEN: Both calls should return the same values
        $this->assertSame($firstStatus, $secondStatus);
        $this->assertSame($firstCount, $secondCount);
    }

    /** @test */
    #[Test]
    public function shouldCreateMultipleIndependentInstances(): void
    {
        // GIVEN: Two PageStatusCountEntity instances with different data
        $entity1 = new PageStatusCountEntity(status: 'active', count: 10);
        $entity2 = new PageStatusCountEntity(status: 'passive', count: 3);

        // THEN: Each instance should hold independent data
        $this->assertNotEquals($entity1->status(), $entity2->status());
        $this->assertNotEquals($entity1->count(), $entity2->count());
        $this->assertSame('active', $entity1->status());
        $this->assertSame('passive', $entity2->status());
    }
}
