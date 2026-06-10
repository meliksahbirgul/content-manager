<?php

declare(strict_types=1);

namespace Source\References\Infrastructure\Persistence;

use DomainException;
use Source\Media\Domain\Entity\MediaEntity;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\References\Domain\Entity\ReferenceEntity;
use Source\References\Domain\Models\Reference as EloquentReference;
use Source\References\Domain\Repository\ReferenceRepository;
use Source\References\Domain\ValueObjects\CreateReference;
use Source\References\Domain\ValueObjects\UpdateReference;

class EloquentReferenceRepository implements ReferenceRepository
{
    public function create(CreateReference $payload): CreateReference
    {
        EloquentReference::create([
            'uuid'  => $payload->id(),
            'name'  => $payload->name(),
            'order' => $payload->order(),
        ]);

        return $payload;
    }

    public function findByUuid(string $uuid, bool $withImages = false): ?ReferenceEntity
    {
        $query = EloquentReference::where('uuid', $uuid);
        if ($withImages) {
            $query->with('images');
        }

        $model = $query->first();
        if (! $model) {
            return null;
        }

        $images = [];
        if ($withImages) {
            /** @var list<MediaEntity> $images */
            $images = array_values(array_map(function ($media) {
                return new MediaEntity(
                    uuid: $media->uuid,
                    mediableType: $media->mediable_type,
                    mediableId: $media->mediable_id,
                    collection: MediaCollection::from($media->collection),
                    disk: MediaDisk::from($media->disk),
                    path: $media->path,
                    url: $media->url,
                    originalName: $media->original_name,
                    mimeType: $media->mime_type,
                    size: $media->size,
                    altText: $media->alt_text,
                    linkPageUuid: $media->link_page_uuid,
                    order: $media->order,
                );
            }, $model->images->all()));
        }

        return new ReferenceEntity(
            id: $model->uuid,
            name: $model->name,
            order: $model->order,
            images: $images,
        );
    }

    public function update(UpdateReference $payload): void
    {
        $model = EloquentReference::where('uuid', $payload->id())->first();
        if (! $model) {
            throw new DomainException('Reference not found.');
        }

        $updateData = [];
        if ($payload->name() !== null) {
            $updateData['name'] = $payload->name();
        }
        if ($payload->order() !== null) {
            $updateData['order'] = $payload->order();
        }

        if (empty($updateData)) {
            return;
        }

        $model->update($updateData);
    }

    public function delete(string $uuid): void
    {
        $model = EloquentReference::where('uuid', $uuid)->first();
        if (! $model) {
            throw new DomainException('Reference not found.');
        }

        $model->delete();
    }

    /** @return array<string, mixed> */
    public function listAll(): array
    {
        return EloquentReference::query()
            ->select(['uuid', 'name', 'order', 'updated_at'])
            ->orderBy('order')
            ->get()
            ->map(fn (EloquentReference $reference) => [
                'id'        => $reference->uuid,
                'name'      => $reference->name,
                'order'     => $reference->order,
                'updatedAt' => $reference->updated_at,
            ])
            ->toArray();
    }
}
