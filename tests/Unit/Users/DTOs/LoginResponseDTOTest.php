<?php

declare(strict_types=1);

namespace Tests\Unit\Users\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\LoginResponseDTO;

class LoginResponseDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithAllParameters(): void
    {
        // GIVEN: Valid login response data
        $email = 'user@example.com';
        $name = 'John Doe';
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...';
        $refreshToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...refresh';
        $expireTime = 3600;

        // WHEN: Creating LoginResponseDTO
        $dto = new LoginResponseDTO(
            email: $email,
            name: $name,
            token: $token,
            refreshToken: $refreshToken,
            expireTime: $expireTime,
        );

        // THEN: Should create instance successfully
        $this->assertInstanceOf(LoginResponseDTO::class, $dto);
    }

    /** @test */
    #[Test]
    public function shouldSerializeToJsonCorrectly(): void
    {
        // GIVEN: Valid login response data
        $email = 'user@example.com';
        $name = 'John Doe';
        $token = 'token123';
        $refreshToken = 'refreshToken123';
        $expireTime = 7200;

        $dto = new LoginResponseDTO(
            email: $email,
            name: $name,
            token: $token,
            refreshToken: $refreshToken,
            expireTime: $expireTime,
        );

        // WHEN: Serializing to JSON
        $json = json_decode(json_encode($dto), true);

        // THEN: Should contain all required fields
        $this->assertEquals($email, $json['email']);
        $this->assertEquals($name, $json['name']);
        $this->assertEquals($token, $json['token']);
        $this->assertEquals($refreshToken, $json['refreshToken']);
        $this->assertEquals($expireTime, $json['expire']);
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleInstances(): void
    {
        // GIVEN: Multiple LoginResponseDTO instances
        $dto1 = new LoginResponseDTO(
            email: 'user1@example.com',
            name: 'User One',
            token: 'token1',
            refreshToken: 'refresh1',
            expireTime: 3600,
        );

        $dto2 = new LoginResponseDTO(
            email: 'user2@example.com',
            name: 'User Two',
            token: 'token2',
            refreshToken: 'refresh2',
            expireTime: 7200,
        );

        // WHEN: Serializing both
        $json1 = json_decode(json_encode($dto1), true);
        $json2 = json_decode(json_encode($dto2), true);

        // THEN: Should have different data
        $this->assertNotEquals($json1['email'], $json2['email']);
        $this->assertNotEquals($json1['token'], $json2['token']);
        $this->assertEquals(3600, $json1['expire']);
        $this->assertEquals(7200, $json2['expire']);
    }

    /** @test */
    #[Test]
    public function shouldHandleSpecialCharactersInNames(): void
    {
        // GIVEN: Name with special characters
        $dto = new LoginResponseDTO(
            email: 'user@example.com',
            name: 'José María Pérez',
            token: 'token123',
            refreshToken: 'refreshToken123',
            expireTime: 3600,
        );

        // WHEN: Serializing
        $json = json_decode(json_encode($dto), true);

        // THEN: Should preserve special characters
        $this->assertEquals('José María Pérez', $json['name']);
    }

    /** @test */
    #[Test]
    public function shouldHandleLongTokens(): void
    {
        // GIVEN: Very long tokens
        $longToken = str_repeat('a', 5000);
        $longRefreshToken = str_repeat('b', 5000);

        $dto = new LoginResponseDTO(
            email: 'user@example.com',
            name: 'Test User',
            token: $longToken,
            refreshToken: $longRefreshToken,
            expireTime: 3600,
        );

        // WHEN: Serializing
        $json = json_decode(json_encode($dto), true);

        // THEN: Should preserve long tokens
        $this->assertEquals($longToken, $json['token']);
        $this->assertEquals($longRefreshToken, $json['refreshToken']);
    }

    /** @test */
    #[Test]
    public function shouldHandleVariousExpireTimeValues(): void
    {
        // GIVEN: Different expire time values
        $testCases = [
            1,
            60,
            3600,
            86400,
            2592000,
            0,
        ];

        foreach ($testCases as $expireTime) {
            $dto = new LoginResponseDTO(
                email: 'user@example.com',
                name: 'Test User',
                token: 'token',
                refreshToken: 'refresh',
                expireTime: $expireTime,
            );

            // WHEN: Serializing
            $json = json_decode(json_encode($dto), true);

            // THEN: Should preserve expire time
            $this->assertEquals($expireTime, $json['expire']);
        }
    }

    /** @test */
    #[Test]
    public function shouldImplementJsonSerializable(): void
    {
        // GIVEN: LoginResponseDTO instance
        $dto = new LoginResponseDTO(
            email: 'user@example.com',
            name: 'Test User',
            token: 'token123',
            refreshToken: 'refreshToken123',
            expireTime: 3600,
        );

        // THEN: Should implement JsonSerializable
        $this->assertInstanceOf(\JsonSerializable::class, $dto);
    }
}
