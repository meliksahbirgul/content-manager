<?php

declare(strict_types=1);

namespace Source\Sliders\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Source\Languages\Application\Services\LanguageService;
use Source\References\Domain\Models\Reference;
use Source\Sliders\Domain\Models\Slider;

class HeroPageViewController extends Controller
{
    public function __construct(private LanguageService $languageService) {}

    public function __invoke(): View
    {
        $sliders = Slider::with('images')->orderBy('order')->get();
        $references = Reference::with('images')->orderBy('order')->get();
        $languages = $this->languageService->listActive();

        return view('panel.hero.edit', compact('sliders', 'references', 'languages'));
    }
}
