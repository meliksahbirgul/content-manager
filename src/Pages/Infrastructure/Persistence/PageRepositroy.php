<?php

declare(strict_types=1);

namespace Source\Pages\Infrastructure\Persistence;

use DomainException;
use Source\Pages\Domain\Entity\PageEntity;
use Source\Pages\Domain\Enums\PageStatus;
use Source\Pages\Domain\Models\Page as EloquentPage;
use Source\Pages\Domain\Repository\Repository;
use Source\Pages\Domain\ValueObjects\CreatePage;
use Source\Pages\Domain\ValueObjects\UpdatePage;

class PageRepositroy implements Repository
{
    public function create(CreatePage $payload): CreatePage
    {
        EloquentPage::create([
            'uuid' => $payload->id(),
            'title' => $payload->title(),
            'content' => $payload->content(),
            'slug' => $payload->slug(),
            'parent_id' => $payload->parentId(),
            'order' => $payload->order(),
            'is_active' => $payload->isActive()->value,
        ]);

        return $payload;
    }

    public function isSlugUnique(array $slugs): bool
    {
        return ! EloquentPage::where(function ($query) use ($slugs) {
            foreach ($slugs as $lang => $value) {
                $query->orWhere("slug->$lang", $value);
            }
        })->exists();
    }

    public function findByUuid(string $uuid): PageEntity|null
    {
        $model = EloquentPage::where('uuid', $uuid)->first();
        if (! $model) {
            return null;
        }

        return new PageEntity(
            id: $model->uuid,
            title: $model->title,
            content: $model->content,
            slug: $model->slug,
            parentId: $model->parentPage?->uuid,
            order: $model->order,
            status: PageStatus::from($model->is_active),
            metadata: $model->metadata ?? null,
        );
    }

    public function updatePage(UpdatePage $payload): void
    {
        $model = EloquentPage::where('uuid', $$payload->id())->first();
        if (! $model) {
            throw new DomainException('Page not found.');
        }

        $updateData = [];
        if ($payload->title() !== null) {
            $updateData['title'] = $payload->title();
        }
        if ($payload->content() !== null) {
            $updateData['content'] = $payload->content();
        }
        if ($payload->slug() !== null) {
            $updateData['slug'] = $payload->slug();
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

    /** @return array<string, mixed> */
    public function listPages(): array
    {
        return EloquentPage::query()
            ->leftJoin('pages as parents', 'pages.parent_id', '=', 'parents.id')
            ->select(
                [
                    'pages.uuid as id',
                    'pages.title as title',
                    'pages.is_active as status',
                    'pages.order',
                    'parents.uuid as parentId',
                ]
            )
            ->get()
            ->toArray();
    }
}
