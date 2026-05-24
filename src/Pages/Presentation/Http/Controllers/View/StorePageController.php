<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Source\Pages\Application\DTOs\CreatePageDTO;
use Source\Pages\Application\Services\PageService;
use Source\Pages\Domain\Enums\PageStatus;

class StorePageController extends Controller
{
    public function __construct(private PageService $pageService) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string|max:255',
            'title.tr' => 'nullable|string|max:255',
            'content' => 'nullable|array',
            'content.*' => 'nullable|string',
            'slug' => 'required|array',
            'slug.en' => 'required|string|max:255',
            'slug.tr' => 'nullable|string|max:255',
            'parent_id' => 'nullable|string|exists:pages,uuid',
        ]);

        // Strip empty strings so they are not stored as blank translations
        $title = array_filter($validated['title'], fn ($v) => $v !== null && $v !== '');
        $slug = array_filter($validated['slug'], fn ($v) => $v !== null && $v !== '');
        $content = array_filter($validated['content'] ?? [], fn ($v) => $v !== null && $v !== '');

        try {
            $dto = CreatePageDTO::fromRequest([
                'title' => $title,
                'content' => $content,
                'slug' => $slug,
                'parentId' => $validated['parent_id'] ?? null,
                'status' => PageStatus::PASSIVE->value,
            ]);

            $this->pageService->createPage($dto);

            return redirect()
                ->route('panel.pages')
                ->with('success', __('panel/pages.draft_saved'));
        } catch (DomainException $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
