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
class ListReferencesControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/panel/references';

    /** @test */
    #[Test]
    public function returns_empty_list_when_no_references(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['status', 'references']);
        $response->assertJsonFragment(['status' => 'success']);
        $this->assertEmpty($response->json('references'));
    }

    /** @test */
    #[Test]
    public function returns_all_references_ordered_by_order(): void
    {
        // GIVEN
        Reference::create(['uuid' => Uuid::uuid7()->toString(), 'name' => 'Beta Inc', 'order' => 2]);
        Reference::create(['uuid' => Uuid::uuid7()->toString(), 'name' => 'Acme Corp', 'order' => 1]);

        // WHEN
        $response = $this->getJson(self::ENDPOINT);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $refs = $response->json('references');
        $this->assertCount(2, $refs);
        $this->assertSame(1, $refs[0]['order']);
        $this->assertSame(2, $refs[1]['order']);
    }
}
