<?php

declare(strict_types=1);

namespace Source\Media\Infrastructure\Persistence;

use Source\Media\Domain\Entity\MediaEntity;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Media\Domain\Models\Media;
use Source\Media\Domain\Repository\MediaRepository;
use Source\Media\Domain\ValueObjects\UploadMedia;
use Source\Pages\Domain\Models\Page;

class EloquentMediaRepository implements MediaRepository
{
    public function save(UploadMedia $payload): MediaEntity
    {
        /** @var Media $model */
        $model = Media::create([
            'uuid' => $payload->uuid(),
            'mediable_type' => $payload->mediableType(),
            'mediable_id' => $payload->mediableId(),
            'collection' => $payload->collection()->value,
            'disk' => $payload->disk()->value,
            'path' => $payload->path(),
            'url' => $payload->url(),
            'original_name' => $payload->originalName(),
            'mime_type' => $payload->mimeType(),
            'size' => $payload->size(),
            'alt_text' => $payload->altText(),
            'link_page_uuid' => $payload->linkPageUuid(),
            'order' => $payload->order(),
        ]);

        return $this->mapToEntity($model);
    }

    public function findByUuid(string $uuid): ?MediaEntity
    {
        $model = Media::where('uuid', $uuid)->first();

        if (! $model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    /** @return list<MediaEntity> */
    public function findForModel(string $mediableType, int $mediableId, ?MediaCollection $collection = null): array
    {
        $query = Media::where('mediable_type', $mediableType)
            ->where('mediable_id', $mediableId)
            ->orderBy('order');

        if ($collection !== null) {
            $query->where('collection', $collection->value);
        }

        $entities = $query->get()->map(fn (Media $m) => $this->mapToEntity($m));

        /** @var list<string> $linkUuids */
        $linkUuids = array_values(
            $entities
                ->filter(fn (MediaEntity $e) => $e->linkPageUuid() !== null)
                ->map(fn (MediaEntity $e) => (string) $e->linkPageUuid())
                ->unique()
                ->values()
                ->all()
        );

        if (! empty($linkUuids)) {
            /** @var array<string, array<string, string>> $slugMap */
            $slugMap = [];
            foreach (Page::whereIn('uuid', $linkUuids)->whereNull('deleted_at')->get(['uuid', 'slug']) as $page) {
                /** @var Page $page */
                $slugMap[(string) $page->uuid] = (array) $page->slug;
            }

            $entities = $entities->map(function (MediaEntity $entity) use ($slugMap): MediaEntity {
                $linkUuid = $entity->linkPageUuid();
                if ($linkUuid === null) {
                    return $entity;
                }

                return $entity->withLinkSlugs($slugMap[$linkUuid] ?? null);
            });
        }

        return array_values($entities->all());
    }

    public function delete(string $uuid): void
    {
        Media::where('uuid', $uuid)->delete();
    }

    private function mapToEntity(Media $model): MediaEntity
    {
        return new MediaEntity(
            uuid: (string) $model->uuid,
            mediableType: (string) $model->mediable_type,
            mediableId: (int) $model->mediable_id,
            collection: MediaCollection::from((string) $model->collection),
            disk: MediaDisk::from((string) $model->disk),
            path: (string) $model->path,
            url: (string) $model->url,
            originalName: (string) $model->original_name,
            mimeType: (string) $model->mime_type,
            size: (int) $model->size,
            altText: isset($model->alt_text) ? (string) $model->alt_text : null,
            linkPageUuid: isset($model->link_page_uuid) ? (string) $model->link_page_uuid : null,
            order: (int) $model->order,
        );
    }
}
