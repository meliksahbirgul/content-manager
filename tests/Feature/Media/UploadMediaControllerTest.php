<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\Media\Domain\Models\Media;
use Source\Pages\Domain\Models\Page;
use Source\Users\Domain\Models\User;
use Tests\TestCase;

#[Group('presentation')]
class UploadMediaControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->page = Page::create([
            'uuid' => Uuid::uuid7()->toString(),
            'title' => ['en' => 'Test Page'],
            'content' => ['en' => ''],
            'slug' => ['en' => 'test-page'],
            'order' => 0,
            'is_active' => 'active',
        ]);
    }

    #[Test]
    public function uploads_image_and_returns201(): void
    {
        // GIVEN: A valid fake image file
        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

        // WHEN: Posting to the upload endpoint as an authenticated user
        $response = $this->actingAs($this->user)
            ->post("/panel/pages/{$this->page->uuid}/media", [
                'file' => $file,
                'collection' => 'images',
            ]);

        // THEN: Returns 201 with media JSON, row exists in DB, file is on disk
        $response->assertStatus(201);
        $response->assertJsonStructure(['id', 'url', 'original_name', 'mime_type', 'size', 'collection', 'order']);

        $this->assertDatabaseHas('media', [
            'mediable_type' => Page::class,
            'mediable_id' => $this->page->id,
            'collection' => 'images',
        ]);

        /** @var Media|null $media */
        $media = Media::where('mediable_id', $this->page->id)->first();
        $this->assertNotNull($media);
        Storage::disk('public')->assertExists((string) $media->path);
    }

    #[Test]
    public function uploads_image_with_optional_fields(): void
    {
        // GIVEN: A fake image and a target link page
        $linkPage = Page::create([
            'uuid' => Uuid::uuid7()->toString(),
            'title' => ['en' => 'About'],
            'content' => ['en' => ''],
            'slug' => ['en' => 'about'],
            'order' => 1,
            'is_active' => 'active',
        ]);
        $file = UploadedFile::fake()->image('banner.jpg');

        // WHEN: Posting with alt_text, link_page_uuid, and order
        $response = $this->actingAs($this->user)
            ->post("/panel/pages/{$this->page->uuid}/media", [
                'file' => $file,
                'collection' => 'images',
                'alt_text' => 'Banner image',
                'link_page_uuid' => $linkPage->uuid,
                'order' => 3,
            ]);

        // THEN: All optional fields are persisted
        $response->assertStatus(201);
        $this->assertDatabaseHas('media', [
            'alt_text' => 'Banner image',
            'link_page_uuid' => $linkPage->uuid,
            'order' => 3,
        ]);
    }

    #[Test]
    public function returns404_for_unknown_page_uuid(): void
    {
        // GIVEN: A UUID that does not correspond to any page
        $file = UploadedFile::fake()->image('photo.jpg');

        // WHEN: Posting to a non-existent page
        $response = $this->actingAs($this->user)
            ->post('/panel/pages/00000000-0000-0000-0000-000000000000/media', [
                'file' => $file,
                'collection' => 'images',
            ]);

        // THEN: 404
        $response->assertStatus(404);
    }

    #[Test]
    public function returns422_for_oversized_file(): void
    {
        // GIVEN: A file that exceeds 10 MB (10240 KiB)
        $file = UploadedFile::fake()->create('big.jpg', 11_000, 'image/jpeg');

        // WHEN: Posting the oversized file (Accept: application/json triggers JSON 422 instead of redirect)
        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post("/panel/pages/{$this->page->uuid}/media", [
                'file' => $file,
                'collection' => 'images',
            ]);

        // THEN: Validation error on file field
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function returns422_for_disallowed_mime_type(): void
    {
        // GIVEN: A PDF file (not an allowed image type)
        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        // WHEN: Posting the disallowed file
        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post("/panel/pages/{$this->page->uuid}/media", [
                'file' => $file,
                'collection' => 'images',
            ]);

        // THEN: Validation error on file field
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function returns422_when_link_page_uuid_does_not_exist(): void
    {
        // GIVEN: A link_page_uuid that references no existing page
        $file = UploadedFile::fake()->image('photo.jpg');
        $nonExistentUuid = Uuid::uuid7()->toString();

        // WHEN: Posting with the invalid link_page_uuid
        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post("/panel/pages/{$this->page->uuid}/media", [
                'file' => $file,
                'collection' => 'images',
                'link_page_uuid' => $nonExistentUuid,
            ]);

        // THEN: Validation error on link_page_uuid field
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['link_page_uuid']);
    }

    #[Test]
    public function redirects_guest_to_login(): void
    {
        // GIVEN: No authenticated user
        $file = UploadedFile::fake()->image('photo.jpg');

        // WHEN: Posting without authentication
        $response = $this->post("/panel/pages/{$this->page->uuid}/media", [
            'file' => $file,
            'collection' => 'images',
        ]);

        // THEN: Redirect to login
        $response->assertRedirect('/login');
    }
}
