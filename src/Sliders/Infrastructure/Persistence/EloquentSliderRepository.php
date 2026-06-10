<?php

declare(strict_types=1);

namespace Source\Sliders\Infrastructure\Persistence;

use DomainException;
use Source\Media\Domain\Entity\MediaEntity;
use Source\Media\Domain\Enums\MediaCollection;
use Source\Media\Domain\Enums\MediaDisk;
use Source\Sliders\Domain\Entity\SliderEntity;
use Source\Sliders\Domain\Enums\SliderStatus;
use Source\Sliders\Domain\Models\Slider as EloquentSlider;
use Source\Sliders\Domain\Repository\SliderRepository;
use Source\Sliders\Domain\ValueObjects\CreateSlider;
use Source\Sliders\Domain\ValueObjects\UpdateSlider;

class EloquentSliderRepository implements SliderRepository
{
    public function create(CreateSlider $payload): CreateSlider
    {
        EloquentSlider::create([
            'uuid'      => $payload->id(),
            'title'     => $payload->title(),
            'href'      => $payload->href(),
            'order'     => $payload->order(),
            'is_active' => $payload->status()->value,
        ]);

        return $payload;
    }

    public function findByUuid(string $uuid, bool $withImages = false): ?SliderEntity
    {
        $query = EloquentSlider::where('uuid', $uuid);
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

        return new SliderEntity(
            id: $model->uuid,
            title: $model->title,
            href: $model->href,
            order: $model->order,
            isActive: SliderStatus::from($model->is_active),
            images: $images,
        );
    }

    public function update(UpdateSlider $payload): void
    {
        $model = EloquentSlider::where('uuid', $payload->id())->first();
        if (! $model) {
            throw new DomainException('Slider not found.');
        }

        $updateData = [];
        if ($payload->title() !== null) {
            $updateData['title'] = $payload->title();
        }
        if ($payload->href() !== null) {
            $updateData['href'] = $payload->href();
        }
        if ($payload->order() !== null) {
            $updateData['order'] = $payload->order();
        }
        if ($payload->status() !== null) {
            $updateData['is_active'] = $payload->status()->value;
        }

        if (empty($updateData)) {
            return;
        }

        $model->update($updateData);
    }

    public function delete(string $uuid): void
    {
        $model = EloquentSlider::where('uuid', $uuid)->first();
        if (! $model) {
            throw new DomainException('Slider not found.');
        }

        $model->delete();
    }

    /** @return array<string, mixed> */
    public function listAll(): array
    {
        return EloquentSlider::query()
            ->select(['uuid', 'title', 'href', 'order', 'is_active', 'updated_at'])
            ->orderBy('order')
            ->get()
            ->map(fn (EloquentSlider $slider) => [
                'id'        => $slider->uuid,
                'title'     => $slider->title,
                'href'      => $slider->href,
                'order'     => $slider->order,
                'isActive'  => $slider->is_active,
                'updatedAt' => $slider->updated_at,
            ])
            ->toArray();
    }
}
