<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Source\Pages\Domain\Models\Page;
use Tests\TestCase;

class ListPagesControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string ENDPOINT = '/api/panel/pages';

    /** @test */
    #[Test]
    public function shouldListPagesSuccessfully(): void
    {
        // Arrange
        $parentUuid = \Ramsey\Uuid\Uuid::uuid7()->toString();
        $parentPage = Page::create([
            'uuid' => $parentUuid,
            'title' => ['en' => 'Parent Page'],
            'content' => ['en' => 'Parent Content'],
            'slug' => ['en' => 'parent-page'],
            'order' => 1,
            'is_active' => 'active',
        ]);

        $childUuid = \Ramsey\Uuid\Uuid::uuid7()->toString();
        Page::create([
            'uuid' => $childUuid,
            'title' => ['en' => 'Child Page'],
            'content' => ['en' => 'Child Content'],
            'slug' => ['en' => 'child-page'],
            'parent_id' => $parentPage->id,
            'order' => 2,
            'is_active' => 'active',
        ]);

        // Act
        $response = $this->getJson(self::ENDPOINT);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'status',
            'pages' => [
                '*' => [
                    'id',
                    'title',
                    'status',
                    'order',
                    'children'
                ]
            ],
        ]);
        
        $response->assertJsonFragment([
            'status' => 'success',
        ]);
        
        $json = $response->json();
        $this->assertCount(1, $json['pages']); // Parent page at root level
        $this->assertEquals($parentUuid, $json['pages'][0]['id']);
        $this->assertCount(1, $json['pages'][0]['children']); // Child page nested
        $this->assertEquals($childUuid, $json['pages'][0]['children'][0]['id']);
    }

    /** @test */
    #[Test]
    public function shouldReturnEmptyListWhenNoPagesExist(): void
    {
        // Act
        $response = $this->getJson(self::ENDPOINT);

        // Assert
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'status' => 'success',
            'pages' => [],
        ]);
    }
}
