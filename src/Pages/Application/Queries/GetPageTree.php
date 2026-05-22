<?php

declare(strict_types=1);

namespace Source\Pages\Application\Queries;

use Source\Pages\Application\DTOs\ListPageDTO;
use Source\Pages\Application\DTOs\PageTreeResponseDTO;
use Source\Pages\Domain\Repository\Repository;

use function usort;

readonly class GetPageTree
{
    public function __construct(private Repository $repository) {}

    /** @return list<PageTreeResponseDTO> */
    public function execute(ListPageDTO $dto): array
    {
        $pages = $this->repository->listPages();

        if ($dto->search() !== null && $dto->search() !== '') {
            $search = $dto->search();
            $lower = strtolower($search);
            $pages = array_values(array_filter(
                $pages,
                fn($page) => is_array($page['title']) &&
                    array_filter($page['title'], fn($t) => str_contains(strtolower((string) $t), $lower)) !== []
            ));
        }

        if ($dto->status() !== null) {
            $pages = array_values(array_filter(
                $pages,
                fn($page) => $page['status'] === $dto->status()->value,
            ));
        }

        return $this->buildTree($pages);
    }

    /**
     * @param array<int<0,max>|string, mixed> $pages
     * @return list<PageTreeResponseDTO>
     */
    private function buildTree(array $pages, string|null $parentId = null): array
    {
        $tree = [];

        foreach ($pages as $page) {
            if ($page['parentId'] === $parentId) {
                $children = $this->buildTree($pages, $page['id']);
                $tree[]   = PageTreeResponseDTO::createFromArray($page, $children);
            }
        }

        usort($tree, fn($a, $b) => $a->order() <=> $b->order());

        return $tree;
    }
}
