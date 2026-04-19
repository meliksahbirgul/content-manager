<?php

declare(strict_types=1);

namespace Source\Pages\Application\Queries;

use Source\Pages\Application\DTOs\PageTreeResponseDTO;
use Source\Pages\Domain\Repository\Repository;

use function usort;

readonly class GetPageTree
{
    public function __construct(private Repository $repository) {}

    /** @return array<string, mixed> */
    public function execute(): array
    {
        $pages = $this->repository->listPages();

        return $this->buildTree($pages);
    }

    /**
     * @param array<string, mixed> $pages
     * @return array<PageTreeResponseDTO>
     */
    private function buildTree(array $pages, string|null $parentId = null): array
    {
        $tree = [];

        foreach ($pages as $page) {
            if ($page->parent_id === $parentId) {
                $children = $this->buildTree($pages, $page->id());
                $tree[]   = PageTreeResponseDTO::createFromArray($page, $children);
            }
        }

        usort($tree, fn($a, $b) => $a->order() <=> $b->order());

        return $tree;
    }
}
