<?php

declare(strict_types=1);

namespace Tests\Unit\Users\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Source\Users\Domain\Models\User;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string LOGOUT_ENDPOINT = '/api/panel/v1/auth/logout';
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
    public function shouldLogoutSuccessfullyWithBearerToken(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser();

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokens['accessToken'])
            ->postJson(self::LOGOUT_ENDPOINT);

        // Assert
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    #[Test]
    public function shouldLogoutSuccessfullyWithBearerTokenAndRefreshToken(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser();

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokens['accessToken'])
            ->postJson(self::LOGOUT_ENDPOINT, [
                'refreshToken' => $tokens['refreshToken'],
            ]);

        // Assert
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    #[Test]
    public function shouldReturnNoContentWhenNoBearerTokenProvided(): void
    {
        // Act - No bearer token means accessToken is null, so logout is skipped
        $response = $this->postJson(self::LOGOUT_ENDPOINT);

        // Assert
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    #[Test]
    public function shouldReturnNoContentWithInvalidBearerToken(): void
    {
        // Act - Invalid bearer token (token doesn't exist in DB)
        $response = $this->withHeader('Authorization', 'Bearer invalidtoken123')
            ->postJson(self::LOGOUT_ENDPOINT);

        // Assert - Controller doesn't validate the token, just calls deleteToken
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    #[Test]
    public function shouldReturnEmptyBodyOnLogout(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser();

        // Act
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokens['accessToken'])
            ->postJson(self::LOGOUT_ENDPOINT);

        // Assert
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getContent());
    }

    /** @test */
    #[Test]
    public function shouldHandleLogoutWithOnlyRefreshTokenInBody(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        $tokens = $this->loginUser();

        // Act - Send refresh token in body along with bearer
        $response = $this->withHeader('Authorization', 'Bearer ' . $tokens['accessToken'])
            ->postJson(self::LOGOUT_ENDPOINT, [
                'refreshToken' => $tokens['refreshToken'],
            ]);

        // Assert
        $response->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    #[Test]
    public function shouldAllowMultipleLogoutsWithDifferentTokens(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Login twice to get two sets of tokens
        $tokens1 = $this->loginUser();
        $tokens2 = $this->loginUser();

        // Act - Logout both sessions
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $tokens1['accessToken'])
            ->postJson(self::LOGOUT_ENDPOINT);
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $tokens2['accessToken'])
            ->postJson(self::LOGOUT_ENDPOINT);

        // Assert
        $response1->assertStatus(Response::HTTP_NO_CONTENT);
        $response2->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /** @test */
    #[Test]
    public function shouldLogoutDifferentUsersIndependently(): void
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
        $response1 = $this->withHeader('Authorization', 'Bearer ' . $tokens1['accessToken'])
            ->postJson(self::LOGOUT_ENDPOINT);
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $tokens2['accessToken'])
            ->postJson(self::LOGOUT_ENDPOINT);

        // Assert
        $response1->assertStatus(Response::HTTP_NO_CONTENT);
        $response2->assertStatus(Response::HTTP_NO_CONTENT);
    }
}
