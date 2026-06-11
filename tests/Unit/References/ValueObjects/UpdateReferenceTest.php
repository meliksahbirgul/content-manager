<?php

declare(strict_types=1);

namespace Tests\Unit\References\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\References\Domain\ValueObjects\UpdateReference;

class UpdateReferenceTest extends TestCase
{
    private string $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = Uuid::uuid7()->toString();
    }

    /** @test */
    #[Test]
    public function creates_with_id_only_fields_nullable(): void
    {
        $ref = new UpdateReference(id: $this->id);

        $this->assertSame($this->id, $ref->id());
        $this->assertNull($ref->name());
        $this->assertNull($ref->order());
    }

    /** @test */
    #[Test]
    public function create_from_array_maps_all_fields(): void
    {
        $ref = UpdateReference::createFromArray([
            'id'    => $this->id,
            'name'  => 'New Corp',
            'order' => 3,
        ]);

        $this->assertSame($this->id, $ref->id());
        $this->assertSame('New Corp', $ref->name());
        $this->assertSame(3, $ref->order());
    }

    /** @test */
    #[Test]
    public function throws_on_invalid_uuid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid UUID format for id.');

        new UpdateReference(id: 'bad-uuid');
    }

    /** @test */
    #[Test]
    public function create_from_array_throws_when_id_missing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "id" field is required.');

        UpdateReference::createFromArray(['name' => 'No ID']);
    }
}
