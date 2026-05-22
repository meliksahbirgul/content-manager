<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Source\Pages\Application\DTOs\ListPageDTO;
use Source\Pages\Application\Queries\GetPageTree;

class CreatePageViewController extends Controller
{
    public function __construct(private GetPageTree $getPageTree) {}

    public function __invoke(): View
    {
        $pages = $this->getPageTree->execute(new ListPageDTO(null, null));

        return view('panel.pages.create', [
            'pages' => $pages,
        ]);
    }
}
