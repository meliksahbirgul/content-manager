<?php

declare(strict_types=1);

namespace Tests\Unit\Users\Services;

use DomainException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\LoginDTO;
use Source\Users\Application\DTOs\RefreshDTO;
use Source\Users\Application\Services\UserService;
use Source\Users\Domain\Repository\Repository;

class UserServiceSadPathTest extends TestCase
{
    /** @var Repository&Mockery\MockInterface */
    private Mockery\MockInterface $repositoryMock;

    private UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(Repository::class);
        $this->userService = new UserService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Helper method to hash a password
     */
    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenRepositoryThrowsOnFindByEmail(): void
    {
        // GIVEN: Repository throws exception on findByEmail
        $email = 'user@example.com';
        $password = 'password123';
        $dto = new LoginDTO(email: $email, password: $password);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andThrow(new \RuntimeException('Database connection failed'));

        // THEN: Should propagate the exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database connection failed');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenRepositoryThrowsOnCreateTokenForUser(): void
    {
        // GIVEN: Repository throws exception on createTokenForUser
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = $this->hashPassword($password);
        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() to return true
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andThrow(new \RuntimeException('Token service unavailable'));

        // THEN: Should propagate the exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token service unavailable');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenRepositoryThrowsOnFindUserByRefreshToken(): void
    {
        // GIVEN: Repository throws exception on findUserByRefreshToken
        $refreshToken = 'refresh123';
        $dto = new RefreshDTO(refreshToken: $refreshToken);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andThrow(new \RuntimeException('Database connection failed'));

        // THEN: Should propagate the exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Database connection failed');

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenRepositoryThrowsOnDeleteToken(): void
    {
        // GIVEN: Repository throws exception on deleteToken
        $refreshToken = 'refresh123';
        $email = 'user@example.com';
        $dto = new RefreshDTO(refreshToken: $refreshToken);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andReturn($userEntity);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->with($refreshToken)
            ->andThrow(new \RuntimeException('Token deletion failed'));

        // THEN: Should propagate the exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token deletion failed');

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenRepositoryThrowsOnCreateTokenForUserDuringRefresh(): void
    {
        // GIVEN: Repository throws exception on createTokenForUser during refresh
        $refreshToken = 'refresh123';
        $email = 'user@example.com';
        $dto = new RefreshDTO(refreshToken: $refreshToken);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andReturn($userEntity);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->with($refreshToken);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andThrow(new \RuntimeException('Token creation failed'));

        // THEN: Should propagate the exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Token creation failed');

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenHashFacadeThrows(): void
    {
        // GIVEN: Hash::check throws exception
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = $this->hashPassword($password);
        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() to throw exception
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andThrow(new \RuntimeException('Hash service error'));

        // THEN: Should propagate the exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Hash service error');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenUserFoundButPasswordCheckFailsSilently(): void
    {
        // GIVEN: User found but password comparison returns false
        $email = 'user@example.com';
        $password = 'wrongpassword';
        $correctPassword = 'correctpassword';
        $hashedPassword = $this->hashPassword($correctPassword);
        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() to return false
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(false);

        // THEN: Should throw DomainException with specific message
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Login credentials are wrong.');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenTokenCreationReturnsEmptyString(): void
    {
        // GIVEN: Token creation returns empty string (invalid token)
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = $this->hashPassword($password);
        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);
        $userEntity->shouldReceive('name')->andReturn('User');

        // Mock token entity with empty string
        $tokenEntity = Mockery::mock('Source\Users\Domain\Entity\UserTokenEntity');
        $tokenEntity->shouldReceive('accessToken')->andReturn('');
        $tokenEntity->shouldReceive('refreshToken')->andReturn('');
        $tokenEntity->shouldReceive('expiresAt')->andReturn(3600);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() to return true
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andReturn($tokenEntity);

        // THEN: Should return response (service doesn't validate token emptiness)
        $response = $this->userService->login($dto);
        $this->assertNotNull($response);
    }

    /** @test */
    #[Test]
    public function shouldNotCreateTokenWhenUserNotFound(): void
    {
        // GIVEN: User not found in repository
        $email = 'user@example.com';
        $password = 'password123';
        $dto = new LoginDTO(email: $email, password: $password);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn(null);

        // Verify createTokenForUser is NOT called
        $this->repositoryMock->shouldNotReceive('createTokenForUser');

        // THEN: Should throw exception
        $this->expectException(DomainException::class);

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldNotDeleteTokenWhenRefreshUserNotFound(): void
    {
        // GIVEN: Refresh token not found
        $refreshToken = 'invalid_token';
        $dto = new RefreshDTO(refreshToken: $refreshToken);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andReturn(null);

        // Verify deleteToken is NOT called
        $this->repositoryMock->shouldNotReceive('deleteToken');

        // THEN: Should throw exception
        $this->expectException(DomainException::class);

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldHandleRaceConditionWhereUserIsDeletedDuringLogin(): void
    {
        // GIVEN: User exists during findByEmail but repository state changes
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = $this->hashPassword($password);
        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() to return true
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        // But createTokenForUser returns null (user deleted)
        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andReturn(null);

        // THEN: Should throw exception about credentials
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credentials failed.');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldHandleRaceConditionWhereUserIsDeletedDuringRefresh(): void
    {
        // GIVEN: Token exists but user is deleted before token creation
        $refreshToken = 'refresh123';
        $email = 'user@example.com';
        $dto = new RefreshDTO(refreshToken: $refreshToken);

        // Mock user entity that exists initially
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andReturn($userEntity);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->with($refreshToken);

        // But createTokenForUser returns null (user/email deleted)
        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andReturn(null);

        // THEN: Should throw exception about credentials
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credentials failed.');

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWithMessageWhenUserNotFoundDuringLogin(): void
    {
        // GIVEN: User not found
        $email = 'nonexistent@example.com';
        $password = 'password123';
        $dto = new LoginDTO(email: $email, password: $password);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn(null);

        // THEN: Should throw with specific message
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User not found.');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWithMessageWhenPasswordIsWrongDuringLogin(): void
    {
        // GIVEN: Wrong password
        $email = 'user@example.com';
        $password = 'wrongpassword';
        $correctPassword = 'correctpassword';
        $hashedPassword = $this->hashPassword($correctPassword);
        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() to return false
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(false);

        // THEN: Should throw with specific message
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Login credentials are wrong.');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWithMessageWhenTokenCreationFailsDuringLogin(): void
    {
        // GIVEN: Token creation fails
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = $this->hashPassword($password);
        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() to return true
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andReturn(null);

        // THEN: Should throw with specific message
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credentials failed.');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWithMessageWhenUserNotFoundDuringRefresh(): void
    {
        // GIVEN: Refresh token user not found
        $refreshToken = 'invalid_refresh_token';
        $dto = new RefreshDTO(refreshToken: $refreshToken);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andReturn(null);

        // THEN: Should throw with specific message
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User not found.');

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWithMessageWhenTokenCreationFailsDuringRefresh(): void
    {
        // GIVEN: Token creation fails during refresh
        $refreshToken = 'refresh123';
        $email = 'user@example.com';
        $dto = new RefreshDTO(refreshToken: $refreshToken);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andReturn($userEntity);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->with($refreshToken);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andReturn(null);

        // THEN: Should throw with specific message
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credentials failed.');

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldSkipHashCheckWhenUserNotFound(): void
    {
        // GIVEN: User not found (should skip password check)
        $email = 'user@example.com';
        $password = 'password123';
        $dto = new LoginDTO(email: $email, password: $password);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn(null);

        // Hash::check should NOT be called
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldNotReceive('check');

        // THEN: Should throw exception
        $this->expectException(DomainException::class);

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldSkipTokenCreationWhenPasswordCheckFails(): void
    {
        // GIVEN: Password check fails
        $email = 'user@example.com';
        $password = 'wrongpassword';
        $correctPassword = 'correctpassword';
        $hashedPassword = $this->hashPassword($correctPassword);
        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() to return false
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(false);

        // createTokenForUser should NOT be called
        $this->repositoryMock->shouldNotReceive('createTokenForUser');

        // THEN: Should throw exception
        $this->expectException(DomainException::class);

        // WHEN: Calling login
        $this->userService->login($dto);
    }
}
