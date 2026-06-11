<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\Sliders\Domain\Models\Slider;
use Tests\TestCase;

#[Group('presentation')]
class DeleteSliderControllerTest extends TestCase
{
    use RefreshDatabase;

    private function getEndpoint(string $sliderId): string
    {
        return "/api/panel/sliders/{$sliderId}";
    }

    /** @test */
    #[Test]
    public function deletes_slider_successfully(): void
    {
        // GIVEN
        $uuid = Uuid::uuid7()->toString();
        Slider::create([
            'uuid'      => $uuid,
            'title'     => ['en' => 'To Delete'],
            'href'      => ['en' => 'https://example.com'],
            'order'     => 0,
            'is_active' => 'active',
        ]);

        // WHEN
        $response = $this->deleteJson($this->getEndpoint($uuid));

        // THEN
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertNull(Slider::where('uuid', $uuid)->first());
    }

    /** @test */
    #[Test]
    public function soft_deletes_slider(): void
    {
        // GIVEN
        $uuid = Uuid::uuid7()->toString();
        Slider::create([
            'uuid'      => $uuid,
            'title'     => ['en' => 'Soft Delete Me'],
            'href'      => ['en' => 'https://example.com'],
            'order'     => 0,
            'is_active' => 'active',
        ]);

        // WHEN
        $this->deleteJson($this->getEndpoint($uuid))->assertStatus(Response::HTTP_NO_CONTENT);

        // THEN — record still exists in DB with deleted_at set
        $this->assertNotNull(Slider::withTrashed()->where('uuid', $uuid)->first()?->deleted_at);
    }

    /** @test */
    #[Test]
    public function returns_bad_request_when_slider_not_found(): void
    {
        $response = $this->deleteJson($this->getEndpoint(Uuid::uuid7()->toString()));

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment(['details' => 'Slider not found.']);
    }
}
