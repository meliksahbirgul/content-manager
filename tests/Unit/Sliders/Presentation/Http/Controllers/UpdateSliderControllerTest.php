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
class UpdateSliderControllerTest extends TestCase
{
    use RefreshDatabase;

    private function getEndpoint(string $sliderId): string
    {
        return "/api/panel/sliders/{$sliderId}";
    }

    private function createSlider(array $overrides = []): Slider
    {
        $slider = Slider::create(array_merge([
            'uuid'      => Uuid::uuid7()->toString(),
            'title'     => ['en' => 'Original Title'],
            'href'      => ['en' => 'https://example.com'],
            'order'     => 0,
            'is_active' => 'active',
        ], $overrides));

        /** @var Slider $slider */
        return $slider;
    }

    /** @test */
    #[Test]
    public function updates_slider_successfully(): void
    {
        // GIVEN
        $slider = $this->createSlider();

        // WHEN
        $response = $this->patchJson($this->getEndpoint($slider->uuid), [
            'title' => ['en' => 'Updated Title'],
            'href'  => ['en' => 'https://example.com/new'],
        ]);

        // THEN
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $fresh = Slider::where('uuid', $slider->uuid)->first();
        $this->assertSame(['en' => 'Updated Title'], $fresh->title);
        $this->assertSame(['en' => 'https://example.com/new'], $fresh->href);
    }

    /** @test */
    #[Test]
    public function partial_update_leaves_other_fields_unchanged(): void
    {
        // GIVEN
        $slider = $this->createSlider(['href' => ['en' => 'https://original.com']]);

        // WHEN — only update title
        $this->patchJson($this->getEndpoint($slider->uuid), [
            'title' => ['en' => 'New Title'],
        ])->assertStatus(Response::HTTP_NO_CONTENT);

        // THEN — href unchanged
        $fresh = Slider::where('uuid', $slider->uuid)->first();
        $this->assertSame(['en' => 'https://original.com'], $fresh->href);
    }

    /** @test */
    #[Test]
    public function returns_bad_request_when_slider_not_found(): void
    {
        $response = $this->patchJson($this->getEndpoint(Uuid::uuid7()->toString()), [
            'title' => ['en' => 'Updated'],
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment(['details' => 'Slider not found.']);
    }

    /** @test */
    #[Test]
    public function fails_validation_when_href_value_is_not_a_url(): void
    {
        $slider = $this->createSlider();

        $response = $this->patchJson($this->getEndpoint($slider->uuid), [
            'href' => ['en' => 'not-a-url'],
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['href.en']);
    }
}
