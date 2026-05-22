<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Source\Pages\Application\DTOs\ListPageDTO;
use Source\Pages\Application\Queries\GetPageTree;

class ListPagesController extends Controller
{
    public function __construct(private GetPageTree $getPageTree) {}

    public function __invoke(Request $request): View
    {
        $pages = $this->getPageTree->execute(ListPageDTO::fromRequest($request->all()));

        return view(
            'panel.pages.index',
            [
                'pages' => $pages,
            ]
        );
    }
}
