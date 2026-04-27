<?php

declare(strict_types=1);

namespace Tests\Unit\Users\Services;

use DomainException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\LoginDTO;
use Source\Users\Application\DTOs\LogoutDTO;
use Source\Users\Application\DTOs\RefreshDTO;
use Source\Users\Application\Services\UserService;
use Source\Users\Domain\Repository\Repository;

class UserServiceTest extends TestCase
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
     * Helper method to hash a password using Laravel's Hash facade
     */
    private function hashPassword(string $password): string
    {
        // Since we're using Mockery, we'll use a simple hash that we can verify
        // In real scenarios, this would use Hash::make()
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Helper method to verify hashed password
     */
    private function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** @test */
    #[Test]
    public function shouldLoginSuccessfully(): void
    {
        // GIVEN: Valid login credentials
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = $this->hashPassword($password);
        $token = 'token123';
        $refreshToken = 'refreshToken123';
        $expireTime = 3600;

        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('name')->andReturn('John Doe');
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        // Mock token entity
        $tokenEntity = Mockery::mock('Source\Users\Domain\Entity\UserTokenEntity');
        $tokenEntity->shouldReceive('accessToken')->andReturn($token);
        $tokenEntity->shouldReceive('refreshToken')->andReturn($refreshToken);
        $tokenEntity->shouldReceive('expiresAt')->andReturn($expireTime);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Mock Hash::check() by mocking the password verification in the actual service
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andReturn($tokenEntity);

        // WHEN: Calling login
        $result = $this->userService->login($dto);

        // THEN: Should return LoginResponseDTO
        $this->assertNotNull($result);
        $json = json_decode(json_encode($result), true);
        $this->assertEquals($email, $json['email']);
        $this->assertEquals('John Doe', $json['name']);
        $this->assertEquals($token, $json['token']);
        $this->assertEquals($refreshToken, $json['refreshToken']);
        $this->assertEquals($expireTime, $json['expire']);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenUserNotFound(): void
    {
        // GIVEN: Non-existent user
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $dto = new LoginDTO(email: $email, password: $password);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn(null);

        $this->repositoryMock->shouldNotReceive('createTokenForUser');

        // THEN: Should throw DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User not found.');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenPasswordIsWrong(): void
    {
        // GIVEN: Wrong password
        $email = 'user@example.com';
        $password = 'wrongpassword';
        $hashedPassword = $this->hashPassword('correctpassword');

        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
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

        $this->repositoryMock->shouldNotReceive('createTokenForUser');

        // THEN: Should throw DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Login credentials are wrong.');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenTokenCreationFails(): void
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

        // Mock Hash::check() to return true
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->with($email)
            ->andReturn(null);

        // THEN: Should throw DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credentials failed.');

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldVerifyRepositoryCallOrderDuringLogin(): void
    {
        // GIVEN: Valid login credentials
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = $this->hashPassword($password);
        $token = 'token123';
        $refreshToken = 'refreshToken123';
        $expireTime = 3600;

        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('name')->andReturn('John Doe');
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        // Mock token entity
        $tokenEntity = Mockery::mock('Source\Users\Domain\Entity\UserTokenEntity');
        $tokenEntity->shouldReceive('accessToken')->andReturn($token);
        $tokenEntity->shouldReceive('refreshToken')->andReturn($refreshToken);
        $tokenEntity->shouldReceive('expiresAt')->andReturn($expireTime);

        // Mock Hash::check() to return true
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->ordered()
            ->with($email)
            ->andReturn($userEntity);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->ordered()
            ->with($email)
            ->andReturn($tokenEntity);

        // WHEN: Calling login
        $result = $this->userService->login($dto);

        // THEN: findByEmail should be called before createTokenForUser
        $this->assertNotNull($result);
    }

    /** @test */
    #[Test]
    public function shouldRefreshTokenSuccessfully(): void
    {
        // GIVEN: Valid refresh token
        $refreshToken = 'refreshToken123';
        $email = 'user@example.com';
        $newToken = 'newToken123';
        $newRefreshToken = 'newRefreshToken123';
        $expireTime = 3600;

        $dto = new RefreshDTO(refreshToken: $refreshToken);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('name')->andReturn('John Doe');

        // Mock token entity
        $tokenEntity = Mockery::mock('Source\Users\Domain\Entity\UserTokenEntity');
        $tokenEntity->shouldReceive('accessToken')->andReturn($newToken);
        $tokenEntity->shouldReceive('refreshToken')->andReturn($newRefreshToken);
        $tokenEntity->shouldReceive('expiresAt')->andReturn($expireTime);

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
            ->andReturn($tokenEntity);

        // WHEN: Calling refresh
        $result = $this->userService->refresh($dto);

        // THEN: Should return new LoginResponseDTO
        $this->assertNotNull($result);
        $json = json_decode(json_encode($result), true);
        $this->assertEquals($email, $json['email']);
        $this->assertEquals('John Doe', $json['name']);
        $this->assertEquals($newToken, $json['token']);
        $this->assertEquals($newRefreshToken, $json['refreshToken']);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenRefreshTokenUserNotFound(): void
    {
        // GIVEN: Invalid refresh token
        $refreshToken = 'invalidRefreshToken';

        $dto = new RefreshDTO(refreshToken: $refreshToken);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andReturn(null);

        $this->repositoryMock->shouldNotReceive('deleteToken');
        $this->repositoryMock->shouldNotReceive('createTokenForUser');

        // THEN: Should throw DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('User not found.');

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenTokenCreationFailsOnRefresh(): void
    {
        // GIVEN: Token creation fails during refresh
        $refreshToken = 'refreshToken123';
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

        // THEN: Should throw DomainException
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Credentials failed.');

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldVerifyRepositoryCallOrderDuringRefresh(): void
    {
        // GIVEN: Valid refresh token
        $refreshToken = 'refreshToken123';
        $email = 'user@example.com';
        $newToken = 'newToken123';
        $newRefreshToken = 'newRefreshToken123';
        $expireTime = 3600;

        $dto = new RefreshDTO(refreshToken: $refreshToken);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('name')->andReturn('John Doe');

        // Mock token entity
        $tokenEntity = Mockery::mock('Source\Users\Domain\Entity\UserTokenEntity');
        $tokenEntity->shouldReceive('accessToken')->andReturn($newToken);
        $tokenEntity->shouldReceive('refreshToken')->andReturn($newRefreshToken);
        $tokenEntity->shouldReceive('expiresAt')->andReturn($expireTime);

        // Verify call order: findUserByRefreshToken -> deleteToken -> createTokenForUser
        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->ordered()
            ->with($refreshToken)
            ->andReturn($userEntity);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->ordered()
            ->with($refreshToken);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->once()
            ->ordered()
            ->with($email)
            ->andReturn($tokenEntity);

        // WHEN: Calling refresh
        $result = $this->userService->refresh($dto);

        // THEN: Should execute in correct order
        $this->assertNotNull($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleLoginWithDifferentPasswords(): void
    {
        // GIVEN: Different passwords for different users
        $testUsers = [
            ['email' => 'user1@example.com', 'password' => 'password1234', 'name' => 'User One'],
            ['email' => 'user2@example.com', 'password' => 'password5678', 'name' => 'User Two'],
            ['email' => 'user3@example.com', 'password' => 'password9012', 'name' => 'User Three'],
        ];

        // Setup Hash mock once before loop
        $hashMock = Mockery::mock('overload:Illuminate\Support\Facades\Hash');

        foreach ($testUsers as $user) {
            $dto = new LoginDTO(email: $user['email'], password: $user['password']);
            $hashedPassword = $this->hashPassword($user['password']);

            // Mock user entity
            $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
            $userEntity->shouldReceive('email')->andReturn($user['email']);
            $userEntity->shouldReceive('name')->andReturn($user['name']);
            $userEntity->shouldReceive('password')->andReturn($hashedPassword);

            // Mock token entity
            $tokenEntity = Mockery::mock('Source\Users\Domain\Entity\UserTokenEntity');
            $tokenEntity->shouldReceive('accessToken')->andReturn('token');
            $tokenEntity->shouldReceive('refreshToken')->andReturn('refresh');
            $tokenEntity->shouldReceive('expiresAt')->andReturn(3600);

            // Mock Hash::check() to return true for this password
            $hashMock->shouldReceive('check')
                ->with($user['password'], $hashedPassword)
                ->andReturn(true);

            $this->repositoryMock
                ->shouldReceive('findByEmail')
                ->with($user['email'])
                ->andReturn($userEntity);

            $this->repositoryMock
                ->shouldReceive('createTokenForUser')
                ->with($user['email'])
                ->andReturn($tokenEntity);

            // WHEN: Calling login
            $result = $this->userService->login($dto);

            // THEN: Should successfully log in
            $this->assertNotNull($result);
        }
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleRefreshTokens(): void
    {
        // GIVEN: Multiple refresh tokens
        $testTokens = [
            ['refreshToken' => 'refresh_token_1', 'email' => 'user1@example.com', 'name' => 'User One'],
            ['refreshToken' => 'refresh_token_2', 'email' => 'user2@example.com', 'name' => 'User Two'],
            ['refreshToken' => 'refresh_token_3', 'email' => 'user3@example.com', 'name' => 'User Three'],
        ];

        foreach ($testTokens as $tokenData) {
            $dto = new RefreshDTO(refreshToken: $tokenData['refreshToken']);

            // Mock user entity
            $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
            $userEntity->shouldReceive('email')->andReturn($tokenData['email']);
            $userEntity->shouldReceive('name')->andReturn($tokenData['name']);

            // Mock token entity
            $tokenEntity = Mockery::mock('Source\Users\Domain\Entity\UserTokenEntity');
            $tokenEntity->shouldReceive('accessToken')->andReturn('new_token');
            $tokenEntity->shouldReceive('refreshToken')->andReturn('new_refresh_token');
            $tokenEntity->shouldReceive('expiresAt')->andReturn(3600);

            $this->repositoryMock
                ->shouldReceive('findUserByRefreshToken')
                ->with($tokenData['refreshToken'])
                ->andReturn($userEntity);

            $this->repositoryMock
                ->shouldReceive('deleteToken')
                ->with($tokenData['refreshToken']);

            $this->repositoryMock
                ->shouldReceive('createTokenForUser')
                ->with($tokenData['email'])
                ->andReturn($tokenEntity);

            // WHEN: Calling refresh
            $result = $this->userService->refresh($dto);

            // THEN: Should successfully refresh token
            $this->assertNotNull($result);
        }
    }

    /** @test */
    #[Test]
    public function shouldNotCreateTokenIfPasswordCheckFails(): void
    {
        // GIVEN: Wrong password
        $email = 'user@example.com';
        $password = 'wrongpassword';
        $hashedPassword = $this->hashPassword('correctpassword');

        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        // Mock Hash::check() to return false
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(false);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->once()
            ->with($email)
            ->andReturn($userEntity);

        // Verify createTokenForUser is NOT called
        $this->repositoryMock->shouldNotReceive('createTokenForUser');

        // THEN: Should throw exception
        $this->expectException(DomainException::class);

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldNotDeleteTokenIfUserNotFoundDuringRefresh(): void
    {
        // GIVEN: Invalid refresh token
        $refreshToken = 'invalidRefreshToken';

        $dto = new RefreshDTO(refreshToken: $refreshToken);

        $this->repositoryMock
            ->shouldReceive('findUserByRefreshToken')
            ->once()
            ->with($refreshToken)
            ->andReturn(null);

        // Verify deleteToken is NOT called
        $this->repositoryMock->shouldNotReceive('deleteToken');
        $this->repositoryMock->shouldNotReceive('createTokenForUser');

        // THEN: Should throw exception
        $this->expectException(DomainException::class);

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldHandleUsersWithSameEmailDifferentNames(): void
    {
        // GIVEN: Same email but different name (edge case)
        $email = 'user@example.com';
        $password = 'password123';
        $hashedPassword = $this->hashPassword($password);
        $name = 'Jane Doe';

        $dto = new LoginDTO(email: $email, password: $password);

        // Mock user entity
        $userEntity = Mockery::mock('Source\Users\Domain\Entity\UserEntity');
        $userEntity->shouldReceive('email')->andReturn($email);
        $userEntity->shouldReceive('name')->andReturn($name);
        $userEntity->shouldReceive('password')->andReturn($hashedPassword);

        // Mock token entity
        $tokenEntity = Mockery::mock('Source\Users\Domain\Entity\UserTokenEntity');
        $tokenEntity->shouldReceive('accessToken')->andReturn('token123');
        $tokenEntity->shouldReceive('refreshToken')->andReturn('refresh123');
        $tokenEntity->shouldReceive('expiresAt')->andReturn(3600);

        // Mock Hash::check() to return true
        Mockery::mock('overload:Illuminate\Support\Facades\Hash')
            ->shouldReceive('check')
            ->with($password, $hashedPassword)
            ->andReturn(true);

        $this->repositoryMock
            ->shouldReceive('findByEmail')
            ->with($email)
            ->andReturn($userEntity);

        $this->repositoryMock
            ->shouldReceive('createTokenForUser')
            ->with($email)
            ->andReturn($tokenEntity);

        // WHEN: Calling login
        $result = $this->userService->login($dto);

        // THEN: Should return correct name
        $json = json_decode(json_encode($result), true);
        $this->assertEquals($name, $json['name']);
    }

    /** @test */
    #[Test]
    public function shouldValidateLoginDTOBeforeProcessing(): void
    {
        // GIVEN: Invalid LoginDTO (empty email)
        $dto = new LoginDTO(email: '', password: 'password123');

        // THEN: Should throw InvalidArgumentException from LoginUser validation
        $this->expectException(\InvalidArgumentException::class);

        // WHEN: Calling login
        $this->userService->login($dto);
    }

    /** @test */
    #[Test]
    public function shouldValidateRefreshDTOBeforeProcessing(): void
    {
        // GIVEN: Invalid RefreshDTO (empty token)
        $dto = new RefreshDTO(refreshToken: '');

        // THEN: Should throw InvalidArgumentException from RefreshUser validation
        $this->expectException(\InvalidArgumentException::class);

        // WHEN: Calling refresh
        $this->userService->refresh($dto);
    }

    /** @test */
    #[Test]
    public function shouldLogoutSuccessfullyWithAccessTokenOnly(): void
    {
        // GIVEN: LogoutDTO with access token only
        $accessToken = 'access_token_12345';
        $dto = new LogoutDTO(accessToken: $accessToken);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->with($accessToken);

        // WHEN: Calling logout
        $result = $this->userService->logout($dto);

        // THEN: Should successfully logout and return void
        $this->assertNull($result);
    }

    /** @test */
    #[Test]
    public function shouldLogoutSuccessfullyWithAccessTokenAndRefreshToken(): void
    {
        // GIVEN: LogoutDTO with both access token and refresh token
        $accessToken = 'access_token_12345';
        $refreshToken = 'refresh_token_67890';
        $dto = new LogoutDTO(accessToken: $accessToken, refreshToken: $refreshToken);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->with($accessToken)
            ->once()
            ->ordered();

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->with($refreshToken)
            ->once()
            ->ordered();

        // WHEN: Calling logout
        $result = $this->userService->logout($dto);

        // THEN: Should successfully logout both tokens
        $this->assertNull($result);
    }

    /** @test */
    #[Test]
    public function shouldVerifyRepositoryCallOrderDuringLogout(): void
    {
        // GIVEN: LogoutDTO with both tokens
        $accessToken = 'access_token_12345';
        $refreshToken = 'refresh_token_67890';
        $dto = new LogoutDTO(accessToken: $accessToken, refreshToken: $refreshToken);

        // Verify access token is deleted before refresh token
        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->with($accessToken)
            ->once()
            ->ordered();

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->with($refreshToken)
            ->once()
            ->ordered();

        // WHEN: Calling logout
        $this->userService->logout($dto);

        // THEN: deleteToken should be called twice in correct order
        // Assertion is implicit through ordered() expectations
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function shouldDeleteOnlyAccessTokenWhenRefreshTokenIsNull(): void
    {
        // GIVEN: LogoutDTO with null refresh token
        $accessToken = 'access_token_12345';
        $dto = new LogoutDTO(accessToken: $accessToken, refreshToken: null);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->with($accessToken);

        // Verify deleteToken is only called once
        $this->repositoryMock->shouldNotReceive('deleteToken')
            ->with(null);

        // WHEN: Calling logout
        $this->userService->logout($dto);

        // THEN: Should only delete access token
        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function shouldHandleLogoutWithVariousAccessTokenFormats(): void
    {
        // GIVEN: Various access token formats
        $accessTokens = [
            'simple_token',
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U',
            'token_with_special_chars_!@#$%',
            'token_with_numbers_123456789',
            'very_long_token_' . str_repeat('a', 256),
        ];

        foreach ($accessTokens as $token) {
            // Reset mock for each iteration
            $this->tearDown();
            $this->setUp();

            $dto = new LogoutDTO(accessToken: $token);

            $this->repositoryMock
                ->shouldReceive('deleteToken')
                ->once()
                ->with($token);

            // WHEN: Calling logout
            $result = $this->userService->logout($dto);

            // THEN: Should handle various token formats
            $this->assertNull($result);
        }
    }

    /** @test */
    #[Test]
    public function shouldHandleLogoutWithVariousRefreshTokenFormats(): void
    {
        // GIVEN: Various refresh token formats
        $refreshTokens = [
            'simple_refresh_token',
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U',
            'refresh_token_with_special_chars_!@#$%',
            'refresh_token_with_numbers_987654321',
            'very_long_refresh_token_' . str_repeat('a', 256),
        ];

        foreach ($refreshTokens as $token) {
            // Reset mock for each iteration
            $this->tearDown();
            $this->setUp();

            $dto = new LogoutDTO(
                accessToken: 'access_token_12345',
                refreshToken: $token
            );

            $this->repositoryMock
                ->shouldReceive('deleteToken')
                ->with('access_token_12345')
                ->once();

            $this->repositoryMock
                ->shouldReceive('deleteToken')
                ->with($token)
                ->once();

            // WHEN: Calling logout
            $result = $this->userService->logout($dto);

            // THEN: Should handle various refresh token formats
            $this->assertNull($result);
        }
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleConsecutiveLogouts(): void
    {
        // GIVEN: Multiple logout requests
        $logoutRequests = [
            new LogoutDTO(accessToken: 'access_token_1', refreshToken: 'refresh_token_1'),
            new LogoutDTO(accessToken: 'access_token_2', refreshToken: 'refresh_token_2'),
            new LogoutDTO(accessToken: 'access_token_3', refreshToken: 'refresh_token_3'),
        ];

        foreach ($logoutRequests as $dto) {
            // Reset mock for each iteration
            $this->tearDown();
            $this->setUp();

            $this->repositoryMock
                ->shouldReceive('deleteToken')
                ->with($dto->accessToken())
                ->once();

            $this->repositoryMock
                ->shouldReceive('deleteToken')
                ->with($dto->refreshToken())
                ->once();

            // WHEN: Calling logout
            $result = $this->userService->logout($dto);

            // THEN: Should successfully logout each request
            $this->assertNull($result);
        }
    }

    /** @test */
    #[Test]
    public function shouldHandleLogoutWithEmptyAccessToken(): void
    {
        // GIVEN: LogoutDTO with empty access token
        $dto = new LogoutDTO(accessToken: '');

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->with('');

        // WHEN: Calling logout
        $result = $this->userService->logout($dto);

        // THEN: Should still call deleteToken (validation happens at repository level)
        $this->assertNull($result);
    }

    /** @test */
    #[Test]
    public function shouldCallDeleteTokenExactlyTwiceWhenBothTokensProvided(): void
    {
        // GIVEN: LogoutDTO with both tokens
        $accessToken = 'access_token_12345';
        $refreshToken = 'refresh_token_67890';
        $dto = new LogoutDTO(accessToken: $accessToken, refreshToken: $refreshToken);

        $deleteTokenCall = 0;

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->andReturnUsing(function () use (&$deleteTokenCall) {
                $deleteTokenCall++;
            });

        // WHEN: Calling logout
        $this->userService->logout($dto);

        // THEN: deleteToken should be called exactly twice
        $this->assertEquals(2, $deleteTokenCall);
    }

    /** @test */
    #[Test]
    public function shouldCallDeleteTokenExactlyOnceWhenOnlyAccessTokenProvided(): void
    {
        // GIVEN: LogoutDTO with only access token
        $accessToken = 'access_token_12345';
        $dto = new LogoutDTO(accessToken: $accessToken);

        $deleteTokenCall = 0;

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->andReturnUsing(function () use (&$deleteTokenCall) {
                $deleteTokenCall++;
            });

        // WHEN: Calling logout
        $this->userService->logout($dto);

        // THEN: deleteToken should be called exactly once
        $this->assertEquals(1, $deleteTokenCall);
    }

    /** @test */
    #[Test]
    public function shouldLogoutReturnVoid(): void
    {
        // GIVEN: LogoutDTO
        $dto = new LogoutDTO(accessToken: 'access_token_12345', refreshToken: 'refresh_token_67890');

        $this->repositoryMock
            ->shouldReceive('deleteToken');

        // WHEN: Calling logout
        $result = $this->userService->logout($dto);

        // THEN: Should return void (null)
        $this->assertNull($result);
        $this->assertIsNotString($result);
        $this->assertIsNotArray($result);
        $this->assertIsNotObject($result);
    }

    /** @test */
    #[Test]
    public function shouldNotThrowExceptionDuringLogout(): void
    {
        // GIVEN: LogoutDTO
        $dto = new LogoutDTO(accessToken: 'access_token_12345', refreshToken: 'refresh_token_67890');

        $this->repositoryMock
            ->shouldReceive('deleteToken');

        // THEN: Should not throw any exception
        $this->expectNotToPerformAssertions();

        // WHEN: Calling logout
        $this->userService->logout($dto);
    }

    /** @test */
    #[Test]
    public function shouldLogoutBeIdempotent(): void
    {
        // GIVEN: Same logout request called multiple times
        $dto = new LogoutDTO(accessToken: 'access_token_12345', refreshToken: 'refresh_token_67890');

        // Call logout 3 times
        for ($i = 0; $i < 3; $i++) {
            $this->tearDown();
            $this->setUp();

            $this->repositoryMock
                ->shouldReceive('deleteToken')
                ->with('access_token_12345')
                ->once();

            $this->repositoryMock
                ->shouldReceive('deleteToken')
                ->with('refresh_token_67890')
                ->once();

            // WHEN: Calling logout
            $result = $this->userService->logout($dto);

            // THEN: Should return same result each time
            $this->assertNull($result);
        }
    }

    /** @test */
    #[Test]
    public function shouldLogoutWithAccessTokenContainingSpaces(): void
    {
        // GIVEN: LogoutDTO with access token containing spaces
        $accessToken = '  access_token_12345  ';
        $dto = new LogoutDTO(accessToken: $accessToken);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->once()
            ->with($accessToken);

        // WHEN: Calling logout
        $result = $this->userService->logout($dto);

        // THEN: Should preserve and delete token with spaces
        $this->assertNull($result);
    }

    /** @test */
    #[Test]
    public function shouldLogoutWithRefreshTokenContainingSpaces(): void
    {
        // GIVEN: LogoutDTO with refresh token containing spaces
        $refreshToken = '  refresh_token_67890  ';
        $dto = new LogoutDTO(accessToken: 'access_token_12345', refreshToken: $refreshToken);

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->with('access_token_12345')
            ->once();

        $this->repositoryMock
            ->shouldReceive('deleteToken')
            ->with($refreshToken)
            ->once();

        // WHEN: Calling logout
        $result = $this->userService->logout($dto);

        // THEN: Should preserve and delete token with spaces
        $this->assertNull($result);
    }
}
