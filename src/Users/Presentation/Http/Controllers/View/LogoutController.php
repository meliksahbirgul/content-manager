<?php

declare(strict_types=1);

namespace Source\Users\Presentation\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Source\Users\Application\Services\UserService;

class LogoutController extends Controller
{
    public function __invoke(Request $request, UserService $userService): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sucessfully logged out.');
    }
}
