<?php

declare(strict_types=1);

namespace Source\Users\Application\Services;

use DomainException;
use Illuminate\Support\Facades\Hash;
use Source\Users\Domain\Repository\Repository;
use Source\Users\Application\DTOs\LoginDTO;
use Source\Users\Application\DTOs\LoginResponseDTO;
use Source\Users\Domain\ValueObjects\LoginUser;

readonly class UserService
{
    public function __construct(
        private Repository $repository
    ) {}

    public function login(LoginDTO $dto): LoginResponseDTO
    {
        $payload = LoginUser::createFromDTO($dto);

        $user = $this->repository->findByEmail($payload->email());
        if (! $user) {
            throw new DomainException('User not found.');
        }

        if (! Hash::check($dto->password(), $user->password())) {
            throw new DomainException('Login credentials are wrong.');
        }

        $token = $this->repository->createTokenForUser($user->email());
        if (! $token) {
            throw new DomainException('Credentials failed.');
        }

        return new LoginResponseDTO(
            email: $user->email(),
            name: $user->name(),
            token: $token->accessToken(),
            refreshToken: $token->refreshToken(),
            expireTime: $token->expiresAt(),
        );
    }
}
