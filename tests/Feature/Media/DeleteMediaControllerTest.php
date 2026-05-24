<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Media\Domain\Models\Media;
use Source\Pages\Domain\Models\Page;
use Source\Users\Domain\Models\User;
use Tests\TestCase;

#[Group('presentation')]
class DeleteMediaControllerTest extends TestCase
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
    public function deletes_media_and_returns204(): void
    {
        // GIVEN: A media row and its corresponding file on the fake disk
        $uuid = Uuid::uuid7()->toString();
        $path = "page/{$this->page->id}/images/{$uuid}.jpg";
        Storage::disk('public')->put($path, 'fake-image-content');

        Media::create([
            'uuid' => $uuid,
            'mediable_type' => Page::class,
            'mediable_id' => $this->page->id,
            'collection' => 'images',
            'disk' => MediaDisk::Public->value,
            'path' => $path,
            'url' => "/storage/{$path}",
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'order' => 0,
        ]);

        // WHEN: Deleting the media by UUID
        $response = $this->actingAs($this->user)
            ->delete("/panel/media/{$uuid}");

        // THEN: Returns 204, DB row is gone, file is removed from disk
        $response->assertStatus(204);
        $this->assertDatabaseMissing('media', ['uuid' => $uuid]);
        Storage::disk('public')->assertMissing($path);
    }

    #[Test]
    public function returns400_for_unknown_media_uuid(): void
    {
        // GIVEN: A UUID that has no media row
        $unknownUuid = Uuid::uuid7()->toString();

        // WHEN: Attempting to delete non-existent media
        $response = $this->actingAs($this->user)
            ->delete("/panel/media/{$unknownUuid}");

        // THEN: Returns 400 with an error message
        $response->assertStatus(400);
        $response->assertJson(['message' => "Media with UUID {$unknownUuid} not found."]);
    }

    #[Test]
    public function redirects_guest_to_login(): void
    {
        // GIVEN: No authenticated user
        $uuid = Uuid::uuid7()->toString();

        // WHEN: Attempting to delete without authentication
        $response = $this->delete("/panel/media/{$uuid}");

        // THEN: Redirect to login
        $response->assertRedirect('/login');
    }
}
