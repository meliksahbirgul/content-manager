<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Source\Pages\Domain\Models\Page;
use Tests\TestCase;

#[Group('presentation')]
class CreatePageControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/panel/pages';

    /** @test */
    #[Test]
    public function shouldCreatePageSuccessfully(): void
    {
        // Arrange
        $payload = [
            'title' => ['en' => 'Test Page', 'tr' => 'Test Sayfa'],
            'content' => ['en' => 'Content', 'tr' => 'İçerik'],
            'slug' => ['en' => 'test-page', 'tr' => 'test-sayfa'],
            'order' => 1,
            'status' => 'active',
        ];

        // Act
        $response = $this->postJson(self::ENDPOINT, $payload);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'status',
            'message',
            'page' => ['id'],
        ]);

        $this->assertDatabaseHas('pages', [
            'order' => 1,
            'is_active' => 'active',
        ]);

        $pageId = $response->json('page.id');
        $page = Page::where('uuid', $pageId)->first();
        $this->assertNotNull($page);
        $this->assertEquals($payload['title'], $page->title);
        $this->assertEquals($payload['slug'], $page->slug);
    }

    /** @test */
    #[Test]
    public function shouldFailValidationWhenTitleIsMissing(): void
    {
        // Arrange
        $payload = [
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'test-page'],
        ];

        // Act
        $response = $this->postJson(self::ENDPOINT, $payload);

        // Assert
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['title']);
    }

    /** @test */
    #[Test]
    public function shouldFailValidationWhenSlugIsMissing(): void
    {
        // Arrange
        $payload = [
            'title' => ['en' => 'Test Page'],
            'content' => ['en' => 'Content'],
        ];

        // Act
        $response = $this->postJson(self::ENDPOINT, $payload);

        // Assert
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors(['slug']);
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestOnServiceException(): void
    {
        // Arrange
        // Create an existing page to cause slug collision
        Page::create([
            'uuid' => \Ramsey\Uuid\Uuid::uuid7()->toString(),
            'title' => ['en' => 'Existing'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'duplicate-slug'],
            'order' => 0,
            'is_active' => 'active',
        ]);

        $payload = [
            'title' => ['en' => 'Test Page'],
            'content' => ['en' => 'Content'],
            'slug' => ['en' => 'duplicate-slug'],
        ];

        // Act
        $response = $this->postJson(self::ENDPOINT, $payload);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'An error occurred while creating the page.',
            'details' => 'This slug is already taken.',
        ]);
    }
}
