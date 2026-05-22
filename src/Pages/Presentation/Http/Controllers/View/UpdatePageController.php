<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Source\Pages\Application\DTOs\UpdatePageDTO;
use Source\Pages\Application\Services\PageService;

class UpdatePageController extends Controller
{
    public function __construct(private PageService $pageService) {}

    public function __invoke(Request $request, string $pageId): RedirectResponse
    {
        $validated = $request->validate([
            'title'     => 'required|array',
            'title.en'  => 'required|string|max:255',
            'title.tr'  => 'nullable|string|max:255',
            'content'   => 'nullable|array',
            'content.*' => 'nullable|string',
            'slug'      => 'required|array',
            'slug.en'   => 'required|string|max:255',
            'slug.tr'   => 'nullable|string|max:255',
            'parent_id' => 'nullable|string|exists:pages,uuid',
            'status'    => 'required|string|in:active,passive',
        ]);

        $title   = array_filter($validated['title'], fn($v) => $v !== null && $v !== '');
        $slug    = array_filter($validated['slug'], fn($v) => $v !== null && $v !== '');
        $content = array_filter($validated['content'] ?? [], fn($v) => $v !== null && $v !== '');

        try {
            $dto = UpdatePageDTO::fromRequest([
                'id'      => $pageId,
                'title'   => $title,
                'content' => $content,
                'slug'    => $slug,
                'status'  => $validated['status'],
            ]);

            $this->pageService->updatePage($dto);

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
