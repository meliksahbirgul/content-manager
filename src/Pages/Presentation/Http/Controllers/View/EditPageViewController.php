<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Source\Pages\Application\DTOs\ListPageDTO;
use Source\Pages\Application\Queries\GetPageForEdit;
use Source\Pages\Application\Queries\GetPageTree;

class EditPageViewController extends Controller
{
    public function __construct(
        private GetPageForEdit $getPageForEdit,
        private GetPageTree $getPageTree,
    ) {}

    public function __invoke(string $pageId): View|RedirectResponse
    {
        try {
            $page  = $this->getPageForEdit->execute($pageId);
            $pages = $this->getPageTree->execute(new ListPageDTO(null, null));

            return view('panel.pages.edit', [
                'page'  => $page,
                'pages' => $pages,
            ]);
        } catch (DomainException) {
            return redirect()
                ->route('panel.pages')
                ->withErrors(['error' => __('panel/pages.not_found')]);
        }
    }
}
