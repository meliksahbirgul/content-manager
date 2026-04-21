<?php

declare(strict_types=1);

namespace Source\Users\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Source\Users\Application\DTOs\LoginDTO;
use Source\Users\Application\DTOs\RefreshDTO;
use Source\Users\Application\Services\UserService;
use Throwable;

class RefreshController extends Controller
{
    public function __invoke(Request $request, UserService $userService): JsonResponse
    {
        $refreshToken = $request->input('refresh_token', null);
        if (! $refreshToken) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Invalid parameters',
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $dto = new RefreshDTO($refreshToken);

            return response()->json($userService->refresh($dto), Response::HTTP_CREATED);
        } catch (Throwable $exception) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Login failed.',
                    'details' => $exception->getMessage(),
                ],
                Response::HTTP_BAD_REQUEST,
            );
        }
    }
}
