<?php

declare(strict_types=1);

namespace Source\Pages\Infrastructure\Persistence;

use Source\Pages\Domain\Models\Page as EloquentPage;
use Source\Pages\Domain\Repository\Repository;
use Source\Pages\Domain\ValueObjects\CreatePage;

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

    public function findByUuid(string $uuid): CreatePage
    {
        $model = EloquentPage::where('uuid', $uuid)->firstOrFail();

        return CreatePage::createFromArray([
            'id' => $model->uuid,
            'title' => $model->title,
            'content' => $model->content,
            'slug' => $model->slug,
            'parentId' => $model->parent_id,
            'order' => $model->order,
            'isActive' => $model->is_active,
        ]);
    }
}
