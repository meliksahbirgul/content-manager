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
class PageDetailsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function getEndpoint(string $pageId): string
    {
        return "/api/panel/pages/{$pageId}";
    }

    /** @test */
    #[Test]
    public function shouldGetPageDetailsSuccessfully(): void
    {
        // Arrange
        $uuid = \Ramsey\Uuid\Uuid::uuid7()->toString();
        Page::create([
            'uuid' => $uuid,
            'title' => ['en' => 'Test Page'],
            'content' => ['en' => 'Test Content'],
            'slug' => ['en' => 'test-page'],
            'order' => 1,
            'is_active' => 'active',
        ]);

        // Act
        $response = $this->getJson($this->getEndpoint($uuid));

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'status',
            'page' => [
                'id',
                'title',
                'content',
                'slug',
                'order',
                'status',
                'parentId',
            ],
        ]);

        $response->assertJsonFragment([
            'status' => 'success',
            'id' => $uuid,
        ]);

        $this->assertEquals(['en' => 'Test Page'], $response->json('page.title'));
        $this->assertEquals(['en' => 'Test Content'], $response->json('page.content'));
        $this->assertEquals(['en' => 'test-page'], $response->json('page.slug'));
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestWhenPageNotFound(): void
    {
        // Act
        $response = $this->getJson($this->getEndpoint(\Ramsey\Uuid\Uuid::uuid7()->toString()));

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Page could not get.',
            'details' => 'Page not found.',
        ]);
    }
}
