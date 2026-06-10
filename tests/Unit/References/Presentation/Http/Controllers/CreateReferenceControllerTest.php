<?php

declare(strict_types=1);

namespace Tests\Unit\References\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Source\References\Domain\Models\Reference;
use Tests\TestCase;

#[Group('presentation')]
class CreateReferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/panel/references';

    /** @test */
    #[Test]
    public function creates_reference_successfully(): void
    {
        // WHEN
        $response = $this->postJson(self::ENDPOINT, ['name' => 'Acme Corp', 'order' => 1]);

        // THEN
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure(['status', 'message', 'reference' => ['id']]);

        $id = $response->json('reference.id');
        $this->assertNotNull(Reference::where('uuid', $id)->first());
    }

    /** @test */
    #[Test]
    public function persists_correct_name_and_order(): void
    {
        $response = $this->postJson(self::ENDPOINT, ['name' => 'Beta Inc', 'order' => 3]);
        $response->assertStatus(Response::HTTP_CREATED);

        $ref = Reference::where('uuid', $response->json('reference.id'))->first();
        $this->assertSame('Beta Inc', $ref->name);
        $this->assertSame(3, $ref->order);
    }

    /** @test */
    #[Test]
    public function fails_validation_when_name_missing(): void
    {
        $response = $this->postJson(self::ENDPOINT, ['order' => 1]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['name']);
    }

    /** @test */
    #[Test]
    public function defaults_order_to_zero_when_not_provided(): void
    {
        $response = $this->postJson(self::ENDPOINT, ['name' => 'Gamma Ltd']);
        $response->assertStatus(Response::HTTP_CREATED);

        $ref = Reference::where('uuid', $response->json('reference.id'))->first();
        $this->assertSame(0, $ref->order);
    }
}
