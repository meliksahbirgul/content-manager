<?php

declare(strict_types=1);

namespace Tests\Unit\Users\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Source\Users\Domain\Models\User;
use Tests\TestCase;

class RefreshControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string REFRESH_ENDPOINT = '/api/panel/v1/auth/refresh';
    private const string LOGIN_ENDPOINT = '/api/panel/v1/auth/login';

    /**
     * Helper to login a user and return access + refresh tokens.
     *
     * @return array{accessToken: string, refreshToken: string}
     */
    private function loginUser(string $email = 'john@example.com', string $password = 'password123'): array
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $email,
            'password' => $password,
        ]);

        return [
            'accessToken' => $response->json('token'),
            'refreshToken' => $response->json('refreshToken'),
        ];
    }

    /** @test */
    #[Test]
    public function shouldRefreshTokenSuccessfully(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser();

        // Act
        $response = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $tokens['refreshToken'],
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'email',
            'name',
            'token',
            'refreshToken',
            'expire',
        ]);
        $response->assertJsonFragment([
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldReturnNewTokensOnRefresh(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $originalTokens = $this->loginUser();

        // Act
        $response = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $originalTokens['refreshToken'],
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $json = $response->json();

        $this->assertNotEmpty($json['token']);
        $this->assertNotEmpty($json['refreshToken']);
        $this->assertNotEquals($originalTokens['accessToken'], $json['token']);
        $this->assertNotEquals($originalTokens['refreshToken'], $json['refreshToken']);
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestWhenRefreshTokenIsMissing(): void
    {
        // Act
        $response = $this->postJson(self::REFRESH_ENDPOINT, []);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Invalid parameters',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestWhenRefreshTokenIsNull(): void
    {
        // Act
        $response = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => null,
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Invalid parameters',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestWhenRefreshTokenIsInvalid(): void
    {
        // Act
        $response = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => 'invalid_token_that_does_not_exist',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Login failed.',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestWithDetailsWhenRefreshTokenIsInvalid(): void
    {
        // Act
        $response = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => 'nonexistent_refresh_token',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonStructure([
            'status',
            'message',
            'details',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldInvalidateOldRefreshTokenAfterRefresh(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser();

        // Act - First refresh should succeed
        $firstRefresh = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $tokens['refreshToken'],
        ]);
        $firstRefresh->assertStatus(Response::HTTP_CREATED);

        // Act - Reusing the same old refresh token should fail
        $secondRefresh = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $tokens['refreshToken'],
        ]);

        // Assert - Old token is deleted, so it should fail
        $secondRefresh->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectUserDataOnRefresh(): void
    {
        // Arrange
        User::create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser('alice@example.com');

        // Act
        $response = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $tokens['refreshToken'],
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonFragment([
            'email' => 'alice@example.com',
            'name' => 'Alice Smith',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldRefreshTokensForDifferentUsersIndependently(): void
    {
        // Arrange
        User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => 'password123',
        ]);
        User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => 'password123',
        ]);

        $tokens1 = $this->loginUser('user1@example.com');
        $tokens2 = $this->loginUser('user2@example.com');

        // Act
        $response1 = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $tokens1['refreshToken'],
        ]);
        $response2 = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $tokens2['refreshToken'],
        ]);

        // Assert
        $response1->assertStatus(Response::HTTP_CREATED);
        $response2->assertStatus(Response::HTTP_CREATED);
        $this->assertEquals('user1@example.com', $response1->json('email'));
        $this->assertEquals('user2@example.com', $response2->json('email'));
    }

    /** @test */
    #[Test]
    public function shouldAllowChainedRefreshes(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser();

        // Act - Chain refreshes: use new refresh token from each response
        $response1 = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $tokens['refreshToken'],
        ]);
        $response1->assertStatus(Response::HTTP_CREATED);

        $response2 = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $response1->json('refreshToken'),
        ]);
        $response2->assertStatus(Response::HTTP_CREATED);

        $response3 = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $response2->json('refreshToken'),
        ]);

        // Assert - All three refreshes should succeed
        $response3->assertStatus(Response::HTTP_CREATED);
        $response3->assertJsonFragment([
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldReturnExpireTimeOnRefresh(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser();

        // Act
        $response = $this->postJson(self::REFRESH_ENDPOINT, [
            'refresh_token' => $tokens['refreshToken'],
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertArrayHasKey('expire', $response->json());
        $this->assertIsInt($response->json('expire'));
    }
}
