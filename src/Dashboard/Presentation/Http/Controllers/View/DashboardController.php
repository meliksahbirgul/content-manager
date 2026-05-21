<?php

declare(strict_types=1);

namespace Source\Dashboard\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Source\Dashboard\Application\Service\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function __invoke(): View
    {
        $dashboard = $this->dashboardService->getDashboard();

        return view('panel.dashboard', [
            'dashboard' => $dashboard,
        ]);
    }
}
