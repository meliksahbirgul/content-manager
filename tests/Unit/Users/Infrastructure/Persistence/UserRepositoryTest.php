<?php

declare(strict_types=1);

namespace Tests\Unit\Users\Infrastructure\Persistence;

use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Source\Users\Domain\Entity\UserEntity;
use Source\Users\Domain\Models\User;
use Source\Users\Infrastructure\Persistence\UserRepository;
use Tests\TestCase;

#[Group('infrastructure')]
class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    /** @test */
    #[Test]
    public function itFindsUserByEmail(): void
    {
        // Arrange
        $email = 'john@example.com';
        $name = 'John Doe';
        $password = bcrypt('password123');

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        // Act
        $user = $this->repository->findByEmail($email);

        // Assert
        $this->assertNotNull($user);
        $this->assertInstanceOf(UserEntity::class, $user);
        $this->assertEquals($name, $user->name());
        $this->assertEquals($email, $user->email());
    }

    /** @test */
    #[Test]
    public function itReturnsNullWhenUserNotFound(): void
    {
        // Arrange
        $email = 'nonexistent@example.com';

        // Act
        $user = $this->repository->findByEmail($email);

        // Assert
        $this->assertNull($user);
    }

    /** @test */
    #[Test]
    public function itCreatesTokensForUser(): void
    {
        // Arrange
        $email = 'jane@example.com';
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        // Act
        $tokenEntity = $this->repository->createTokenForUser($email);

        // Assert
        $this->assertNotNull($tokenEntity);
        $this->assertIsString($tokenEntity->accessToken());
        $this->assertIsString($tokenEntity->refreshToken());
        $this->assertIsInt($tokenEntity->expiresAt());
        $this->assertGreaterThan(0, $tokenEntity->expiresAt());

        // Verify tokens are created in database
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'access-token',
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'refresh-token',
        ]);
    }

    /** @test */
    #[Test]
    public function itReturnsNullWhenCreatingTokenForNonexistentUser(): void
    {
        // Arrange
        $email = 'nonexistent@example.com';

        // Act
        $tokenEntity = $this->repository->createTokenForUser($email);

        // Assert
        $this->assertNull($tokenEntity);
    }

    /** @test */
    #[Test]
    public function itCreatesTokensWithCorrectAbilities(): void
    {
        // Arrange
        $email = 'test@example.com';
        $user = User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => bcrypt('password123'),
        ]);

        // Act
        $this->repository->createTokenForUser($email);

        // Assert
        $accessToken = PersonalAccessToken::where('name', 'access-token')
            ->where('tokenable_id', $user->id)
            ->first();
        $this->assertTrue($accessToken->can('access-panel'));

        $refreshToken = PersonalAccessToken::where('name', 'refresh-token')
            ->where('tokenable_id', $user->id)
            ->first();
        $this->assertTrue($refreshToken->can('issue-access-token'));
    }

    /** @test */
    #[Test]
    public function itFindsUserByValidRefreshToken(): void
    {
        // Arrange
        $email = 'user@example.com';
        $name = 'Test User';
        $password = bcrypt('password123');

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $token = $user->createToken(
            name: 'refresh-token',
            abilities: ['issue-access-token'],
            expiresAt: (new DateTimeImmutable())->modify('+1 hour')
        );

        // Act
        $foundUser = $this->repository->findUserByRefreshToken($token->plainTextToken);

        // Assert
        $this->assertNotNull($foundUser);
        $this->assertInstanceOf(UserEntity::class, $foundUser);
        $this->assertEquals($name, $foundUser->name());
        $this->assertEquals($email, $foundUser->email());
    }

    /** @test */
    #[Test]
    public function itReturnsNullForInvalidRefreshToken(): void
    {
        // Act
        $user = $this->repository->findUserByRefreshToken('invalid_token');

        // Assert
        $this->assertNull($user);
    }

    /** @test */
    #[Test]
    public function itReturnsNullForTokenWithWrongAbility(): void
    {
        // Arrange
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $token = $user->createToken(
            name: 'access-token',
            abilities: ['access-panel'],
            expiresAt: (new DateTimeImmutable())->modify('+1 hour')
        );

        // Act
        $foundUser = $this->repository->findUserByRefreshToken($token->plainTextToken);

        // Assert
        $this->assertNull($foundUser);
    }

    /** @test */
    #[Test]
    public function itReturnsNullForExpiredRefreshToken(): void
    {
        // Arrange
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $token = $user->createToken(
            name: 'refresh-token',
            abilities: ['issue-access-token'],
            expiresAt: (new DateTimeImmutable())->modify('-1 hour')
        );

        // Act
        $foundUser = $this->repository->findUserByRefreshToken($token->plainTextToken);

        // Assert
        $this->assertNull($foundUser);
    }

    /** @test */
    #[Test]
    public function itReturnsNullWhenTokenHasNoTokenable(): void
    {
        // Arrange
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $token = $user->createToken(
            name: 'refresh-token',
            abilities: ['issue-access-token'],
            expiresAt: (new DateTimeImmutable())->modify('+1 hour')
        );

        // Delete the user to orphan the token
        $user->delete();

        // Act
        $foundUser = $this->repository->findUserByRefreshToken($token->plainTextToken);

        // Assert
        $this->assertNull($foundUser);
    }

    /** @test */
    #[Test]
    public function itDeletesTokenSuccessfully(): void
    {
        // Arrange
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $token = $user->createToken(
            name: 'access-token',
            abilities: ['access-panel']
        );

        $plainTextToken = $token->plainTextToken;
        $tokenModel = PersonalAccessToken::findToken($plainTextToken);
        $this->assertNotNull($tokenModel);

        // Act
        $this->repository->deleteToken($plainTextToken);

        // Assert
        $deletedToken = PersonalAccessToken::findToken($plainTextToken);
        $this->assertNull($deletedToken);
    }

    /** @test */
    #[Test]
    public function itHandlesDeletionOfNonexistentToken(): void
    {
        // Act & Assert (should not throw exception)
        $this->repository->deleteToken('nonexistent_token');

        $this->assertTrue(true);
    }

    /** @test */
    #[Test]
    public function itMapsEloquentUserToUserEntity(): void
    {
        // Arrange
        $name = 'Mapped User';
        $email = 'mapped@example.com';
        $password = bcrypt('password123');

        $eloquentUser = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        // Act
        $userEntity = $this->repository->findByEmail($email);

        // Assert
        $this->assertInstanceOf(UserEntity::class, $userEntity);
        $this->assertEquals($name, $userEntity->name());
        $this->assertEquals($email, $userEntity->email());
        $this->assertEquals($eloquentUser->password, $userEntity->password());
    }
}
