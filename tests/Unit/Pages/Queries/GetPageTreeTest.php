<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Queries;

use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Application\DTOs\PageTreeResponseDTO;
use Source\Pages\Application\Queries\GetPageTree;
use Source\Pages\Domain\Repository\Repository;

class GetPageTreeTest extends TestCase
{
    /** @var Repository&Mockery\MockInterface */
    private Mockery\MockInterface $repositoryMock;

    private GetPageTree $getPageTree;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(Repository::class);
        $this->getPageTree = new GetPageTree($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    #[Test]
    public function shouldReturnEmptyArrayWhenNoPagesExist(): void
    {
        // GIVEN: Empty page list
        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn([]);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Should return empty array
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnArrayOfPageTreeResponseDTO(): void
    {
        // GIVEN: Single page without children
        $pageId = Uuid::uuid7()->toString();
        $pages = [
            [
                'id' => $pageId,
                'title' => ['en' => 'Root Page'],
                'status' => 'active',
                'order' => 0,
                'parentId' => null,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Should return array with one item
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(PageTreeResponseDTO::class, $result[0]);
        $this->assertEquals($pageId, $result[0]->id());
        $this->assertEquals('Root Page', $result[0]->title()['en']);
    }

    /** @test */
    #[Test]
    public function shouldBuildTreeWithChildren(): void
    {
        // GIVEN: Parent and child pages
        $parentId = Uuid::uuid7()->toString();
        $childId1 = Uuid::uuid7()->toString();
        $childId2 = Uuid::uuid7()->toString();

        $pages = [
            [
                'id' => $parentId,
                'title' => ['en' => 'Parent'],
                'status' => 'active',
                'order' => 0,
                'parentId' => null,
            ],
            [
                'id' => $childId1,
                'title' => ['en' => 'Child 1'],
                'status' => 'active',
                'order' => 1,
                'parentId' => $parentId,
            ],
            [
                'id' => $childId2,
                'title' => ['en' => 'Child 2'],
                'status' => 'passive',
                'order' => 2,
                'parentId' => $parentId,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Should build tree structure correctly
        $this->assertCount(1, $result);
        $parent = $result[0];
        $this->assertEquals($parentId, $parent->id());
        $this->assertCount(2, $parent->children());
        $this->assertEquals('Child 1', $parent->children()[0]->title()['en']);
        $this->assertEquals('Child 2', $parent->children()[1]->title()['en']);
    }

    /** @test */
    #[Test]
    public function shouldBuildNestedTreeMultipleLevels(): void
    {
        // GIVEN: Multi-level page hierarchy
        $rootId = Uuid::uuid7()->toString();
        $parentId = Uuid::uuid7()->toString();
        $childId = Uuid::uuid7()->toString();
        $grandchildId = Uuid::uuid7()->toString();

        $pages = [
            [
                'id' => $rootId,
                'title' => ['en' => 'Root'],
                'status' => 'active',
                'order' => 0,
                'parentId' => null,
            ],
            [
                'id' => $parentId,
                'title' => ['en' => 'Parent'],
                'status' => 'active',
                'order' => 1,
                'parentId' => $rootId,
            ],
            [
                'id' => $childId,
                'title' => ['en' => 'Child'],
                'status' => 'active',
                'order' => 2,
                'parentId' => $parentId,
            ],
            [
                'id' => $grandchildId,
                'title' => ['en' => 'Grandchild'],
                'status' => 'active',
                'order' => 3,
                'parentId' => $childId,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Should build multi-level tree correctly
        $this->assertCount(1, $result);
        $root = $result[0];
        $this->assertEquals('Root', $root->title()['en']);
        $this->assertCount(1, $root->children());

        $parent = $root->children()[0];
        $this->assertEquals('Parent', $parent->title()['en']);
        $this->assertCount(1, $parent->children());

        $child = $parent->children()[0];
        $this->assertEquals('Child', $child->title()['en']);
        $this->assertCount(1, $child->children());

        $grandchild = $child->children()[0];
        $this->assertEquals('Grandchild', $grandchild->title()['en']);
    }

    /** @test */
    #[Test]
    public function shouldSortChildrenByOrder(): void
    {
        // GIVEN: Pages with different order values
        $parentId = Uuid::uuid7()->toString();
        $child1Id = Uuid::uuid7()->toString();
        $child2Id = Uuid::uuid7()->toString();
        $child3Id = Uuid::uuid7()->toString();

        $pages = [
            [
                'id' => $parentId,
                'title' => ['en' => 'Parent'],
                'status' => 'active',
                'order' => 0,
                'parentId' => null,
            ],
            [
                'id' => $child1Id,
                'title' => ['en' => 'Child 3'],
                'status' => 'active',
                'order' => 3,
                'parentId' => $parentId,
            ],
            [
                'id' => $child2Id,
                'title' => ['en' => 'Child 1'],
                'status' => 'active',
                'order' => 1,
                'parentId' => $parentId,
            ],
            [
                'id' => $child3Id,
                'title' => ['en' => 'Child 2'],
                'status' => 'active',
                'order' => 2,
                'parentId' => $parentId,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Children should be sorted by order
        $parent = $result[0];
        $children = $parent->children();
        $this->assertEquals(1, $children[0]->order());
        $this->assertEquals(2, $children[1]->order());
        $this->assertEquals(3, $children[2]->order());
        $this->assertEquals('Child 1', $children[0]->title()['en']);
        $this->assertEquals('Child 2', $children[1]->title()['en']);
        $this->assertEquals('Child 3', $children[2]->title()['en']);
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleRootPages(): void
    {
        // GIVEN: Multiple root level pages
        $root1Id = Uuid::uuid7()->toString();
        $root2Id = Uuid::uuid7()->toString();

        $pages = [
            [
                'id' => $root1Id,
                'title' => ['en' => 'Root 1'],
                'status' => 'active',
                'order' => 1,
                'parentId' => null,
            ],
            [
                'id' => $root2Id,
                'title' => ['en' => 'Root 2'],
                'status' => 'active',
                'order' => 2,
                'parentId' => null,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Should return multiple root pages sorted by order
        $this->assertCount(2, $result);
        $this->assertEquals('Root 1', $result[0]->title()['en']);
        $this->assertEquals('Root 2', $result[1]->title()['en']);
    }

    /** @test */
    #[Test]
    public function shouldPreservePageData(): void
    {
        // GIVEN: Page with all attributes
        $pageId = Uuid::uuid7()->toString();
        $pages = [
            [
                'id' => $pageId,
                'title' => ['en' => 'Test', 'tr' => 'Test TR'],
                'status' => 'active',
                'order' => 5,
                'parentId' => null,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: All page data should be preserved
        $page = $result[0];
        $this->assertEquals($pageId, $page->id());
        $this->assertEquals('active', $page->status());
        $this->assertEquals(5, $page->order());
        $this->assertEquals(['en' => 'Test', 'tr' => 'Test TR'], $page->title());
    }

    /** @test */
    #[Test]
    public function shouldHandlePassivePages(): void
    {
        // GIVEN: Mix of active and passive pages
        $activePage = Uuid::uuid7()->toString();
        $passivePage = Uuid::uuid7()->toString();

        $pages = [
            [
                'id' => $activePage,
                'title' => ['en' => 'Active'],
                'status' => 'active',
                'order' => 1,
                'parentId' => null,
            ],
            [
                'id' => $passivePage,
                'title' => ['en' => 'Passive'],
                'status' => 'passive',
                'order' => 2,
                'parentId' => null,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Should include both active and passive pages
        $this->assertCount(2, $result);
        $this->assertEquals('active', $result[0]->status());
        $this->assertEquals('passive', $result[1]->status());
    }

    /** @test */
    #[Test]
    public function shouldEmptyChildrenArrayWhenNoChildren(): void
    {
        // GIVEN: Page without children
        $pageId = Uuid::uuid7()->toString();
        $pages = [
            [
                'id' => $pageId,
                'title' => ['en' => 'Leaf Page'],
                'status' => 'active',
                'order' => 0,
                'parentId' => null,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Children should be empty array
        $page = $result[0];
        $this->assertEmpty($page->children());
        $this->assertIsArray($page->children());
    }

    /** @test */
    #[Test]
    public function shouldCallRepositoryListPagesOnce(): void
    {
        // GIVEN: Empty page list
        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn([]);

        // WHEN: Executing query
        $this->getPageTree->execute();

        // THEN: Repository method was called exactly once
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function shouldReturnArrayType(): void
    {
        // GIVEN: Pages
        $pages = [
            [
                'id' => Uuid::uuid7()->toString(),
                'title' => ['en' => 'Page'],
                'status' => 'active',
                'order' => 0,
                'parentId' => null,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Result should be array
        $this->assertIsArray($result);
    }

    /** @test */
    #[Test]
    public function shouldBeReadonlyClass(): void
    {
        // THEN: GetPageTree should be readonly
        $this->assertInstanceOf(GetPageTree::class, $this->getPageTree);
        $reflection = new \ReflectionClass(GetPageTree::class);
        $this->assertTrue($reflection->isReadonly(), 'GetPageTree should be readonly');
    }

    /** @test */
    #[Test]
    public function shouldHandleMixedOrderWithSiblings(): void
    {
        // GIVEN: Siblings with various order values
        $parentId = Uuid::uuid7()->toString();
        $pages = [
            [
                'id' => $parentId,
                'title' => ['en' => 'Parent'],
                'status' => 'active',
                'order' => 0,
                'parentId' => null,
            ],
            [
                'id' => Uuid::uuid7()->toString(),
                'title' => ['en' => 'Child with order 5'],
                'status' => 'active',
                'order' => 5,
                'parentId' => $parentId,
            ],
            [
                'id' => Uuid::uuid7()->toString(),
                'title' => ['en' => 'Child with order 1'],
                'status' => 'active',
                'order' => 1,
                'parentId' => $parentId,
            ],
            [
                'id' => Uuid::uuid7()->toString(),
                'title' => ['en' => 'Child with order 3'],
                'status' => 'active',
                'order' => 3,
                'parentId' => $parentId,
            ],
        ];

        $this->repositoryMock
            ->shouldReceive('listPages')
            ->once()
            ->andReturn($pages);

        // WHEN: Executing query
        $result = $this->getPageTree->execute();

        // THEN: Children should be sorted correctly
        $parent = $result[0];
        $children = $parent->children();
        $this->assertEquals(1, $children[0]->order());
        $this->assertEquals(3, $children[1]->order());
        $this->assertEquals(5, $children[2]->order());
    }
}
