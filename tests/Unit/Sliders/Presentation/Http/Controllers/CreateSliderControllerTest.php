<?php

declare(strict_types=1);

namespace Tests\Unit\Sliders\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Source\Sliders\Domain\Models\Slider;
use Tests\TestCase;

#[Group('presentation')]
class CreateSliderControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/panel/sliders';

    private function validPayload(): array
    {
        return [
            'title'  => ['en' => 'Hero Banner', 'tr' => 'Ana Afiş'],
            'href'   => ['en' => 'https://example.com/en', 'tr' => 'https://example.com/tr'],
            'order'  => 1,
            'status' => 'active',
        ];
    }

    /** @test */
    #[Test]
    public function creates_slider_successfully(): void
    {
        // WHEN
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());

        // THEN
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure(['status', 'message', 'slider' => ['id']]);
        $response->assertJsonFragment(['status' => 'success']);

        $id = $response->json('slider.id');
        $this->assertNotNull(Slider::where('uuid', $id)->first());
    }

    /** @test */
    #[Test]
    public function persists_multilingual_title_and_href(): void
    {
        $response = $this->postJson(self::ENDPOINT, $this->validPayload());
        $response->assertStatus(Response::HTTP_CREATED);

        $slider = Slider::where('uuid', $response->json('slider.id'))->first();
        $this->assertSame(['en' => 'Hero Banner', 'tr' => 'Ana Afiş'], $slider->title);
        $this->assertSame(['en' => 'https://example.com/en', 'tr' => 'https://example.com/tr'], $slider->href);
    }

    /** @test */
    #[Test]
    public function fails_validation_when_title_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['title']);

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['title']);
    }

    /** @test */
    #[Test]
    public function fails_validation_when_href_missing(): void
    {
        $payload = $this->validPayload();
        unset($payload['href']);

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['href']);
    }

    /** @test */
    #[Test]
    public function fails_validation_when_href_value_is_not_a_url(): void
    {
        $payload = $this->validPayload();
        $payload['href'] = ['en' => 'not-a-url'];

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['href.en']);
    }

    /** @test */
    #[Test]
    public function returns_bad_request_on_invalid_language_code(): void
    {
        $payload = $this->validPayload();
        $payload['title'] = ['xx' => 'Bad Lang'];

        $response = $this->postJson(self::ENDPOINT, $payload);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment(['status' => 'error']);
    }
}
