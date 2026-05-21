<?php

declare(strict_types=1);

namespace Source\Users\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Source\Users\Application\DTOs\LoginDTO;
use Source\Users\Application\Services\UserService;
use Throwable;

use function response;

class LoginController extends Controller
{
    public function __invoke(Request $request, UserService $userService): RedirectResponse
    {
        try {
            $dto = LoginDTO::fromRequest($request->all());

            $credentials = $userService->getUser($dto);
            Auth::guard('web')->login($credentials, true);

            $request->session()->regenerate(true);

            return redirect()->intended(route('panel.dashboard'));
        } catch (Throwable $exception) {
            return back()
                ->withErrors(['error' => __('auth.failed')])
                ->withInput($request->only('email', 'remember'));
        }
    }
}
