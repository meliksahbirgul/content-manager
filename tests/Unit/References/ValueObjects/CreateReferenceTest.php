<?php

declare(strict_types=1);

namespace Tests\Unit\References\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\References\Domain\ValueObjects\CreateReference;

class CreateReferenceTest extends TestCase
{
    private string $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = Uuid::uuid7()->toString();
    }

    /** @test */
    #[Test]
    public function creates_with_valid_data(): void
    {
        $ref = new CreateReference(id: $this->id, name: 'Acme Corp', order: 1);

        $this->assertSame($this->id, $ref->id());
        $this->assertSame('Acme Corp', $ref->name());
        $this->assertSame(1, $ref->order());
    }

    /** @test */
    #[Test]
    public function create_from_array_generates_uuid_when_absent(): void
    {
        $ref = CreateReference::createFromArray(['name' => 'Test Co']);

        $this->assertTrue(Uuid::isValid($ref->id()));
        $this->assertSame(0, $ref->order());
    }

    /** @test */
    #[Test]
    public function throws_on_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for id.');

        new CreateReference(id: 'not-a-uuid', name: 'Test', order: 0);
    }

    /** @test */
    #[Test]
    public function throws_on_empty_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Name cannot be empty.');

        new CreateReference(id: $this->id, name: '', order: 0);
    }
}
