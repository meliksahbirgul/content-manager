<?php

declare(strict_types=1);

namespace Tests\Unit\Users\Presentation\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Source\Users\Domain\Models\User;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string LOGIN_ENDPOINT = '/api/panel/v1/auth/login';

    /** @test */
    #[Test]
    public function shouldLoginSuccessfullyWithValidCredentials(): void
    {
        // Arrange
        $password = 'password123';
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => $password,
        ]);

        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => $password,
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
    public function shouldReturnTokenAndRefreshTokenOnLogin(): void
    {
        // Arrange
        $password = 'securePassword456';
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => $password,
        ]);

        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => $user->email,
            'password' => $password,
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_CREATED);
        $json = $response->json();
        $this->assertNotEmpty($json['token']);
        $this->assertNotEmpty($json['refreshToken']);
        $this->assertIsInt($json['expire']);
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestWhenEmailIsMissing(): void
    {
        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'password' => 'password123',
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
    public function shouldReturnBadRequestWhenPasswordIsMissing(): void
    {
        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'john@example.com',
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
    public function shouldReturnBadRequestWhenBothFieldsAreMissing(): void
    {
        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, []);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Login failed.',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestWhenUserNotFound(): void
    {
        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Login failed.',
        ]);
        $response->assertJsonFragment([
            'details' => 'User not found.',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldReturnBadRequestWhenPasswordIsWrong(): void
    {
        // Arrange
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'correctpassword',
        ]);

        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Login failed.',
        ]);
        $response->assertJsonFragment([
            'details' => 'Login credentials are wrong.',
        ]);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectUserDataOnLogin(): void
    {
        // Arrange
        $password = 'password123';
        User::create([
            'name' => 'Alice Smith',
            'email' => 'alice@example.com',
            'password' => $password,
        ]);

        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'alice@example.com',
            'password' => $password,
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
    public function shouldReturnBadRequestWithDetailsOnException(): void
    {
        // Act
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'test@example.com',
            'password' => 'test',
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
    public function shouldAllowMultipleLoginsForSameUser(): void
    {
        // Arrange
        $password = 'password123';
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => $password,
        ]);

        // Act - Login twice
        $response1 = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'john@example.com',
            'password' => $password,
        ]);
        $response2 = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'john@example.com',
            'password' => $password,
        ]);

        // Assert - Both should succeed
        $response1->assertStatus(Response::HTTP_CREATED);
        $response2->assertStatus(Response::HTTP_CREATED);

        // Tokens should be different
        $this->assertNotEquals(
            $response1->json('token'),
            $response2->json('token'),
        );
    }

    /** @test */
    #[Test]
    public function shouldLoginDifferentUsersIndependently(): void
    {
        // Arrange
        $password = 'password123';
        User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => $password,
        ]);
        User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => $password,
        ]);

        // Act
        $response1 = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'user1@example.com',
            'password' => $password,
        ]);
        $response2 = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => 'user2@example.com',
            'password' => $password,
        ]);

        // Assert
        $response1->assertStatus(Response::HTTP_CREATED);
        $response2->assertStatus(Response::HTTP_CREATED);
        $this->assertEquals('user1@example.com', $response1->json('email'));
        $this->assertEquals('user2@example.com', $response2->json('email'));
    }
}
