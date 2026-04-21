<?php

declare(strict_types=1);

namespace Source\Pages\Presentation\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Source\Users\Application\DTOs\LoginDTO;
use Source\Users\Application\Services\UserService;
use Throwable;

class LoginController extends Controller
{
    public function __invoke(Request $request, UserService $userService): JsonResponse
    {
        try {
            $dto = LoginDTO::fromRequest($request->all());

            return response()->json($userService->login($dto), Response::HTTP_CREATED);
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
