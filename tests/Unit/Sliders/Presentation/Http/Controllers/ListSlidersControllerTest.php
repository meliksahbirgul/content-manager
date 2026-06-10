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
class ListSlidersControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/panel/sliders';

    /** @test */
    #[Test]
    public function returns_empty_list_when_no_sliders(): void
    {
        $response = $this->getJson(self::ENDPOINT);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure(['status', 'sliders']);
        $response->assertJsonFragment(['status' => 'success']);
        $this->assertEmpty($response->json('sliders'));
    }

    /** @test */
    #[Test]
    public function returns_all_sliders_ordered_by_order(): void
    {
        // GIVEN
        Slider::create([
            'uuid'      => Uuid::uuid7()->toString(),
            'title'     => ['en' => 'Second'],
            'href'      => ['en' => 'https://example.com/second'],
            'order'     => 2,
            'is_active' => 'active',
        ]);
        Slider::create([
            'uuid'      => Uuid::uuid7()->toString(),
            'title'     => ['en' => 'First'],
            'href'      => ['en' => 'https://example.com/first'],
            'order'     => 1,
            'is_active' => 'active',
        ]);

        // WHEN
        $response = $this->getJson(self::ENDPOINT);

        // THEN
        $response->assertStatus(Response::HTTP_OK);
        $sliders = $response->json('sliders');
        $this->assertCount(2, $sliders);
        $this->assertSame(1, $sliders[0]['order']);
        $this->assertSame(2, $sliders[1]['order']);
    }
}
