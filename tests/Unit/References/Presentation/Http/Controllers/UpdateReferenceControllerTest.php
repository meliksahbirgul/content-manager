<?php

declare(strict_types=1);

namespace Tests\Unit\References\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\References\Domain\Models\Reference;
use Tests\TestCase;

#[Group('presentation')]
class UpdateReferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    private function getEndpoint(string $referenceId): string
    {
        return "/api/panel/references/{$referenceId}";
    }

    private function createReference(array $overrides = []): Reference
    {
        $ref = Reference::create(array_merge([
            'uuid'  => Uuid::uuid7()->toString(),
            'name'  => 'Original Name',
            'order' => 0,
        ], $overrides));

        /** @var Reference $ref */
        return $ref;
    }

    /** @test */
    #[Test]
    public function updates_reference_successfully(): void
    {
        // GIVEN
        $ref = $this->createReference();

        // WHEN
        $response = $this->patchJson($this->getEndpoint($ref->uuid), ['name' => 'Updated Name']);

        // THEN
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertSame('Updated Name', Reference::where('uuid', $ref->uuid)->first()->name);
    }

    /** @test */
    #[Test]
    public function partial_update_leaves_other_fields_unchanged(): void
    {
        // GIVEN
        $ref = $this->createReference(['order' => 5]);

        // WHEN — only update name
        $this->patchJson($this->getEndpoint($ref->uuid), ['name' => 'New Name'])
            ->assertStatus(Response::HTTP_NO_CONTENT);

        // THEN — order unchanged
        $this->assertSame(5, Reference::where('uuid', $ref->uuid)->first()->order);
    }

    /** @test */
    #[Test]
    public function returns_bad_request_when_reference_not_found(): void
    {
        $response = $this->patchJson($this->getEndpoint(Uuid::uuid7()->toString()), [
            'name' => 'Updated',
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment(['details' => 'Reference not found.']);
    }

    /** @test */
    #[Test]
    public function fails_validation_when_name_exceeds_max_length(): void
    {
        $ref = $this->createReference();

        $response = $this->patchJson($this->getEndpoint($ref->uuid), [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['name']);
    }
}
