<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Source\Pages\Domain\Models\Page;
use Tests\TestCase;

class ChangePageDetailsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function getEndpoint(string $pageId): string
    {
        return "/api/panel/pages/{$pageId}";
    }

    /** @test */
    #[Test]
    public function shouldUpdatePageSuccessfully(): void
    {
        // Arrange
        $uuid = \Ramsey\Uuid\Uuid::uuid7()->toString();
        Page::create([
            'uuid' => $uuid,
            'title' => ['en' => 'Old Title'],
            'content' => ['en' => 'Old Content'],
            'slug' => ['en' => 'old-slug'],
            'order' => 1,
            'is_active' => 'active',
        ]);

        $payload = [
            'title' => ['en' => 'New Title'],
            'slug' => ['en' => 'new-slug'],
        ];

        // Act
        $response = $this->patchJson($this->getEndpoint($uuid), $payload);

        // Assert
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $page = Page::where('uuid', $uuid)->first();
        $this->assertEquals(['en' => 'New Title'], $page->title);
        $this->assertEquals(['en' => 'new-slug'], $page->slug);
        $this->assertEquals(['en' => 'Old Content'], $page->content); // Unchanged
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestOnServiceException(): void
    {
        // Arrange
        $uuid1 = \Ramsey\Uuid\Uuid::uuid7()->toString();
        Page::create([
            'uuid' => $uuid1,
            'title' => ['en' => 'Page 1'],
            'content' => ['en' => 'Content 1'],
            'slug' => ['en' => 'duplicate-slug'],
            'order' => 1,
            'is_active' => 'active',
        ]);

        $uuid2 = \Ramsey\Uuid\Uuid::uuid7()->toString();
        Page::create([
            'uuid' => $uuid2,
            'title' => ['en' => 'Page 2'],
            'content' => ['en' => 'Content 2'],
            'slug' => ['en' => 'page-2'],
            'order' => 2,
            'is_active' => 'active',
        ]);

        $payload = [
            'slug' => ['en' => 'duplicate-slug'], // Attempt to use existing slug
        ];

        // Act
        $response = $this->patchJson($this->getEndpoint($uuid2), $payload);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'An error occurred while updating the page.',
            'details' => 'This slug is already taken.',
        ]);
    }
    
    /** @test */
    #[Test]
    public function shouldReturnBadRequestWhenPageNotFound(): void
    {
        // Arrange
        $payload = [
            'title' => ['en' => 'New Title'],
        ];

        // Act
        $response = $this->patchJson($this->getEndpoint(\Ramsey\Uuid\Uuid::uuid7()->toString()), $payload);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'An error occurred while updating the page.',
            'details' => 'Page not found.',
        ]);
    }
}
