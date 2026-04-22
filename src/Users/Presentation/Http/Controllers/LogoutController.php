<?php

declare(strict_types=1);

namespace Source\Users\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Source\Users\Application\DTOs\LogoutDTO;
use Source\Users\Application\Services\UserService;

class LogoutController extends Controller
{
    public function __invoke(Request $request, UserService $userService): JsonResponse
    {
        $accessToken  = $request->bearerToken();
        $refreshToken = $request->input('refreshToken', null);
        if ($accessToken) {
            $userService->logout(new LogoutDTO($accessToken, $refreshToken));
        }

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
