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
class DeleteReferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    private function getEndpoint(string $referenceId): string
    {
        return "/api/panel/references/{$referenceId}";
    }

    /** @test */
    #[Test]
    public function deletes_reference_successfully(): void
    {
        // GIVEN
        $uuid = Uuid::uuid7()->toString();
        Reference::create(['uuid' => $uuid, 'name' => 'To Delete', 'order' => 0]);

        // WHEN
        $response = $this->deleteJson($this->getEndpoint($uuid));

        // THEN
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertNull(Reference::where('uuid', $uuid)->first());
    }

    /** @test */
    #[Test]
    public function soft_deletes_reference(): void
    {
        // GIVEN
        $uuid = Uuid::uuid7()->toString();
        Reference::create(['uuid' => $uuid, 'name' => 'Soft Delete Me', 'order' => 0]);

        // WHEN
        $this->deleteJson($this->getEndpoint($uuid))->assertStatus(Response::HTTP_NO_CONTENT);

        // THEN — record still in DB with deleted_at set
        $this->assertNotNull(Reference::withTrashed()->where('uuid', $uuid)->first()?->deleted_at);
    }

    /** @test */
    #[Test]
    public function returns_bad_request_when_reference_not_found(): void
    {
        $response = $this->deleteJson($this->getEndpoint(Uuid::uuid7()->toString()));

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment(['details' => 'Reference not found.']);
    }
}
