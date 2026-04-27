<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Pages\Application\DTOs\PageTreeResponseDTO;

class PageTreeResponseDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithRequiredParameters(): void
    {
        // GIVEN: Required parameters
        $id = Uuid::uuid7()->toString();
        $title = ['en' => 'Test Title'];
        $status = 'active';
        $order = 0;

        // WHEN: Creating PageTreeResponseDTO
        $dto = new PageTreeResponseDTO(
            id: $id,
            title: $title,
            status: $status,
            order: $order,
        );

        // THEN: Should create instance successfully
        $this->assertInstanceOf(PageTreeResponseDTO::class, $dto);
        $this->assertEquals($id, $dto->id());
        $this->assertEquals($title, $dto->title());
        $this->assertEquals($status, $dto->status());
        $this->assertEquals($order, $dto->order());
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithChildren(): void
    {
        // GIVEN: Parent and children DTOs
        $parentId = Uuid::uuid7()->toString();
        $childId1 = Uuid::uuid7()->toString();
        $childId2 = Uuid::uuid7()->toString();

        $child1 = new PageTreeResponseDTO(
            id: $childId1,
            title: ['en' => 'Child 1'],
            status: 'active',
            order: 1,
        );

        $child2 = new PageTreeResponseDTO(
            id: $childId2,
            title: ['en' => 'Child 2'],
            status: 'passive',
            order: 2,
        );

        // WHEN: Creating parent with children
        $parent = new PageTreeResponseDTO(
            id: $parentId,
            title: ['en' => 'Parent'],
            status: 'active',
            order: 0,
            children: [$child1, $child2],
        );

        // THEN: Should have children
        $this->assertCount(2, $parent->children());
        $this->assertEquals($childId1, $parent->children()[0]->id());
        $this->assertEquals($childId2, $parent->children()[1]->id());
    }

    /** @test */
    #[Test]
    public function shouldCreateFromArrayWithMinimalData(): void
    {
        // GIVEN: Minimal data array
        $id = Uuid::uuid7()->toString();
        $data = [
            'id' => $id,
            'title' => ['en' => 'Test'],
            'status' => 'active',
            'order' => 0,
        ];

        // WHEN: Creating from array
        $dto = PageTreeResponseDTO::createFromArray($data);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(PageTreeResponseDTO::class, $dto);
        $this->assertEquals($id, $dto->id());
    }

    /** @test */
    #[Test]
    public function shouldCreateFromArrayWithChildren(): void
    {
        // GIVEN: Data with children
        $parentId = Uuid::uuid7()->toString();
        $childId = Uuid::uuid7()->toString();

        $data = [
            'id' => $parentId,
            'title' => ['en' => 'Parent'],
            'status' => 'active',
            'order' => 0,
        ];

        $childData = [
            'id' => $childId,
            'title' => ['en' => 'Child'],
            'status' => 'active',
            'order' => 1,
        ];

        $childDto = PageTreeResponseDTO::createFromArray($childData);

        // WHEN: Creating from array with children
        $dto = PageTreeResponseDTO::createFromArray($data, [$childDto]);

        // THEN: Should have children
        $this->assertCount(1, $dto->children());
    }

    /** @test */
    #[Test]
    public function shouldJsonSerializeWithoutChildren(): void
    {
        // GIVEN: PageTreeResponseDTO without children
        $id = Uuid::uuid7()->toString();
        $title = ['en' => 'Test Page', 'tr' => 'Test Sayfası'];
        $status = 'active';
        $order = 5;

        $dto = new PageTreeResponseDTO(
            id: $id,
            title: $title,
            status: $status,
            order: $order,
        );

        // WHEN: Calling jsonSerialize
        $result = $dto->jsonSerialize();

        // THEN: Should return array with all data
        $this->assertIsArray($result);
        $this->assertEquals($id, $result['id']);
        $this->assertEquals($title, $result['title']);
        $this->assertEquals($status, $result['status']);
        $this->assertEquals($order, $result['order']);
        $this->assertEmpty($result['children']);
    }

    /** @test */
    #[Test]
    public function shouldJsonSerializeWithChildren(): void
    {
        // GIVEN: PageTreeResponseDTO with children
        $parentId = Uuid::uuid7()->toString();
        $childId1 = Uuid::uuid7()->toString();
        $childId2 = Uuid::uuid7()->toString();

        $child1 = new PageTreeResponseDTO(
            id: $childId1,
            title: ['en' => 'Child 1'],
            status: 'active',
            order: 1,
        );

        $child2 = new PageTreeResponseDTO(
            id: $childId2,
            title: ['en' => 'Child 2'],
            status: 'passive',
            order: 2,
        );

        $parent = new PageTreeResponseDTO(
            id: $parentId,
            title: ['en' => 'Parent'],
            status: 'active',
            order: 0,
            children: [$child1, $child2],
        );

        // WHEN: Calling jsonSerialize
        $result = $parent->jsonSerialize();

        // THEN: Should include children as DTOs
        $this->assertIsArray($result);
        $this->assertEquals($parentId, $result['id']);
        $this->assertCount(2, $result['children']);
        $this->assertInstanceOf(PageTreeResponseDTO::class, $result['children'][0]);
        $this->assertInstanceOf(PageTreeResponseDTO::class, $result['children'][1]);
        $this->assertEquals($childId1, $result['children'][0]->id());
        $this->assertEquals($childId2, $result['children'][1]->id());
    }

    /** @test */
    #[Test]
    public function shouldJsonSerializeNestedChildren(): void
    {
        // GIVEN: Multi-level page hierarchy
        $rootId = Uuid::uuid7()->toString();
        $parentId = Uuid::uuid7()->toString();
        $childId = Uuid::uuid7()->toString();

        $grandchild = new PageTreeResponseDTO(
            id: $childId,
            title: ['en' => 'Grandchild'],
            status: 'active',
            order: 2,
        );

        $parent = new PageTreeResponseDTO(
            id: $parentId,
            title: ['en' => 'Parent'],
            status: 'active',
            order: 1,
            children: [$grandchild],
        );

        $root = new PageTreeResponseDTO(
            id: $rootId,
            title: ['en' => 'Root'],
            status: 'active',
            order: 0,
            children: [$parent],
        );

        // WHEN: Calling jsonSerialize
        $result = $root->jsonSerialize();

        // THEN: Should preserve nested structure
        $this->assertCount(1, $result['children']);
        $this->assertInstanceOf(PageTreeResponseDTO::class, $result['children'][0]);
        $this->assertCount(1, $result['children'][0]->children());
        $this->assertEquals('Grandchild', $result['children'][0]->children()[0]->title()['en']);
    }

    /** @test */
    #[Test]
    public function shouldBeJsonSerializable(): void
    {
        // GIVEN: PageTreeResponseDTO
        $dto = new PageTreeResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Test'],
            status: 'active',
            order: 0,
        );

        // WHEN: Converting to JSON
        $json = json_encode($dto);

        // THEN: Should be valid JSON
        $this->assertNotFalse($json);
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('Test', $decoded['title']['en']);
    }

    /** @test */
    #[Test]
    public function shouldJsonSerializeMultilingualData(): void
    {
        // GIVEN: PageTreeResponseDTO with multilingual title
        $title = ['en' => 'English', 'tr' => 'Turkish', 'es' => 'Spanish'];
        $dto = new PageTreeResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: $title,
            status: 'active',
            order: 1,
        );

        // WHEN: Calling jsonSerialize
        $result = $dto->jsonSerialize();

        // THEN: Should preserve all languages
        $this->assertCount(3, $result['title']);
        $this->assertEquals('English', $result['title']['en']);
        $this->assertEquals('Turkish', $result['title']['tr']);
        $this->assertEquals('Spanish', $result['title']['es']);
    }

    /** @test */
    #[Test]
    public function shouldJsonSerializeContainAllKeys(): void
    {
        // GIVEN: PageTreeResponseDTO
        $dto = new PageTreeResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title'],
            status: 'active',
            order: 3,
        );

        // WHEN: Calling jsonSerialize
        $result = $dto->jsonSerialize();

        // THEN: Should contain all required keys
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('order', $result);
        $this->assertArrayHasKey('children', $result);
    }

    /** @test */
    #[Test]
    public function shouldHandlePassiveStatus(): void
    {
        // GIVEN: PageTreeResponseDTO with passive status
        $dto = new PageTreeResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Title'],
            status: 'passive',
            order: 0,
        );

        // WHEN: Calling jsonSerialize
        $result = $dto->jsonSerialize();

        // THEN: Should preserve passive status
        $this->assertEquals('passive', $result['status']);
    }

    /** @test */
    #[Test]
    public function shouldHandleVariousOrderValues(): void
    {
        // GIVEN: Various order values
        $orderValues = [0, 1, 10, 100, 999];

        foreach ($orderValues as $order) {
            $dto = new PageTreeResponseDTO(
                id: Uuid::uuid7()->toString(),
                title: ['en' => 'Title'],
                status: 'active',
                order: $order,
            );

            // WHEN: Calling jsonSerialize
            $result = $dto->jsonSerialize();

            // THEN: Should preserve order value
            $this->assertEquals($order, $result['order']);
        }
    }

    /** @test */
    #[Test]
    public function shouldJsonSerializeEmptyChildrenArray(): void
    {
        // GIVEN: PageTreeResponseDTO without children
        $dto = new PageTreeResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Leaf Page'],
            status: 'active',
            order: 0,
        );

        // WHEN: Calling jsonSerialize
        $result = $dto->jsonSerialize();

        // THEN: Should have empty children array
        $this->assertIsArray($result['children']);
        $this->assertEmpty($result['children']);
    }

    /** @test */
    #[Test]
    public function shouldJsonSerializePreservesOrder(): void
    {
        // GIVEN: Multiple children with different orders
        $parent = new PageTreeResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Parent'],
            status: 'active',
            order: 0,
            children: [
                new PageTreeResponseDTO(
                    id: Uuid::uuid7()->toString(),
                    title: ['en' => 'First'],
                    status: 'active',
                    order: 1,
                ),
                new PageTreeResponseDTO(
                    id: Uuid::uuid7()->toString(),
                    title: ['en' => 'Second'],
                    status: 'active',
                    order: 2,
                ),
                new PageTreeResponseDTO(
                    id: Uuid::uuid7()->toString(),
                    title: ['en' => 'Third'],
                    status: 'active',
                    order: 3,
                ),
            ],
        );

        // WHEN: Calling jsonSerialize
        $result = $parent->jsonSerialize();

        // THEN: Children order should be preserved
        $this->assertEquals(1, $result['children'][0]->order());
        $this->assertEquals(2, $result['children'][1]->order());
        $this->assertEquals(3, $result['children'][2]->order());
    }

    /** @test */
    #[Test]
    public function shouldJsonEncodingProduceValidJson(): void
    {
        // GIVEN: Complex PageTreeResponseDTO structure
        $child = new PageTreeResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Child', 'tr' => 'Çocuk'],
            status: 'active',
            order: 1,
        );

        $parent = new PageTreeResponseDTO(
            id: Uuid::uuid7()->toString(),
            title: ['en' => 'Parent', 'tr' => 'Ebeveyn'],
            status: 'passive',
            order: 0,
            children: [$child],
        );

        // WHEN: Encoding to JSON
        $json = json_encode($parent);
        $decoded = json_decode($json, true);

        // THEN: Should produce valid JSON and maintain structure
        $this->assertNotFalse($json);
        $this->assertEquals('Parent', $decoded['title']['en']);
        $this->assertEquals('Ebeveyn', $decoded['title']['tr']);
        $this->assertCount(1, $decoded['children']);
    }
}
