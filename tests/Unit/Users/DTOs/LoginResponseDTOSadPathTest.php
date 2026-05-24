<?php

declare(strict_types=1);

namespace Tests\Unit\Users\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Users\Application\DTOs\LoginResponseDTO;

class LoginResponseDTOSadPathTest extends TestCase
{
    /** @test */
    #[Test]
    public function should_handle_empty_email(): void
    {
        // GIVEN: Empty email
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: '',
            name: 'User',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: 3600,
        );

        // THEN: Should create DTO
        $this->assertEquals('', $dto->jsonSerialize()['email']);
    }

    /** @test */
    #[Test]
    public function should_handle_empty_name(): void
    {
        // GIVEN: Empty name
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: '',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: 3600,
        );

        // THEN: Should create DTO
        $this->assertEquals('', $dto->jsonSerialize()['name']);
    }

    /** @test */
    #[Test]
    public function should_handle_empty_token(): void
    {
        // GIVEN: Empty token
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: 'User',
            token: '',
            refreshToken: 'refresh123',
            expireTime: 3600,
        );

        // THEN: Should create DTO
        $this->assertEquals('', $dto->jsonSerialize()['token']);
    }

    /** @test */
    #[Test]
    public function should_handle_empty_refresh_token(): void
    {
        // GIVEN: Empty refresh token
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: 'User',
            token: 'token123',
            refreshToken: '',
            expireTime: 3600,
        );

        // THEN: Should create DTO
        $this->assertEquals('', $dto->jsonSerialize()['refreshToken']);
    }

    /** @test */
    #[Test]
    public function should_handle_zero_expire_time(): void
    {
        // GIVEN: Zero expire time
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: 'User',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: 0,
        );

        // THEN: Should create DTO
        $this->assertEquals(0, $dto->jsonSerialize()['expire']);
    }

    /** @test */
    #[Test]
    public function should_handle_negative_expire_time(): void
    {
        // GIVEN: Negative expire time (edge case)
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: 'User',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: -1,
        );

        // THEN: Should create DTO
        $this->assertEquals(-1, $dto->jsonSerialize()['expire']);
    }

    /** @test */
    #[Test]
    public function should_handle_very_large_expire_time(): void
    {
        // GIVEN: Very large expire time
        $largeTime = PHP_INT_MAX;
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: 'User',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: $largeTime,
        );

        // THEN: Should create DTO
        $this->assertEquals($largeTime, $dto->jsonSerialize()['expire']);
    }

    /** @test */
    #[Test]
    public function should_handle_very_long_email(): void
    {
        // GIVEN: Very long email
        $longEmail = str_repeat('a', 500).'@example.com';
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: $longEmail,
            name: 'User',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: 3600,
        );

        // THEN: Should create DTO and serialize correctly
        $data = $dto->jsonSerialize();
        $this->assertEquals($longEmail, $data['email']);
    }

    /** @test */
    #[Test]
    public function should_handle_very_long_name(): void
    {
        // GIVEN: Very long name
        $longName = str_repeat('A', 1000);
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: $longName,
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: 3600,
        );

        // THEN: Should serialize correctly
        $data = $dto->jsonSerialize();
        $this->assertEquals($longName, $data['name']);
    }

    /** @test */
    #[Test]
    public function should_handle_special_characters_in_all_fields(): void
    {
        // GIVEN: Special characters in all fields
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user+tag@example.co.uk',
            name: 'John Doe\'s "Official" Name',
            token: 'token!@#$%^&*()',
            refreshToken: 'refresh|<>?:{}[]\\',
            expireTime: 3600,
        );

        // THEN: Should preserve all special characters
        $data = $dto->jsonSerialize();
        $this->assertStringContainsString('+', $data['email']);
        $this->assertStringContainsString('"', $data['name']);
        $this->assertStringContainsString('!@#$%^&*()', $data['token']);
        $this->assertStringContainsString('|<>?:{}[]\\', $data['refreshToken']);
    }

    /** @test */
    #[Test]
    public function should_handle_unicode_in_all_fields(): void
    {
        // GIVEN: Unicode characters
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@例え.jp',
            name: '佐藤 太郎',
            token: 'токен123',
            refreshToken: 'განახლება456',
            expireTime: 3600,
        );

        // THEN: Should preserve unicode
        $data = $dto->jsonSerialize();
        $this->assertStringContainsString('例え', $data['email']);
        $this->assertStringContainsString('佐藤', $data['name']);
        $this->assertStringContainsString('токен', $data['token']);
        $this->assertStringContainsString('განახლება', $data['refreshToken']);
    }

    /** @test */
    #[Test]
    public function should_json_serialize_to_correct_key_names(): void
    {
        // GIVEN: LoginResponseDTO with specific values
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: 'John Doe',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: 7200,
        );

        // WHEN: Serializing to JSON
        $json = json_encode($dto);
        $data = json_decode($json, true);

        // THEN: Should have correct key names (note: expireTime becomes 'expire')
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refreshToken', $data);
        $this->assertArrayHasKey('expire', $data);

        // AND: Should NOT have internal key names
        $this->assertArrayNotHasKey('expireTime', $data);
    }

    /** @test */
    #[Test]
    public function should_handle_whitespace_in_all_fields(): void
    {
        // GIVEN: Whitespace in all fields
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: '   spaces@example.com   ',
            name: '   Name With Spaces   ',
            token: '   token   ',
            refreshToken: '   refresh   ',
            expireTime: 3600,
        );

        // THEN: Should preserve whitespace
        $data = $dto->jsonSerialize();
        $this->assertEquals('   spaces@example.com   ', $data['email']);
        $this->assertEquals('   Name With Spaces   ', $data['name']);
    }

    /** @test */
    #[Test]
    public function should_handle_newlines_in_fields(): void
    {
        // GIVEN: Newlines in text fields
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: "John\nDoe",
            token: "token\nwith\nnewlines",
            refreshToken: "refresh\ntoken",
            expireTime: 3600,
        );

        // THEN: Should serialize newlines correctly
        $json = json_encode($dto);
        $this->assertStringContainsString('\\n', $json);
    }

    /** @test */
    #[Test]
    public function should_handle_tabs_in_fields(): void
    {
        // GIVEN: Tabs in fields
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: "John\tDoe",
            token: "token\twith\ttabs",
            refreshToken: "refresh\ttoken",
            expireTime: 3600,
        );

        // THEN: Should serialize tabs correctly
        $json = json_encode($dto);
        $this->assertStringContainsString('\\t', $json);
    }

    /** @test */
    #[Test]
    public function should_handle_quotes_in_names(): void
    {
        // GIVEN: Quotes in name
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: 'O\'Brien "The Great"',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: 3600,
        );

        // THEN: Should serialize quotes correctly
        $json = json_encode($dto);
        $data = json_decode($json, true);
        $this->assertStringContainsString("O'Brien", $data['name']);
    }

    /** @test */
    #[Test]
    public function should_multiple_instances_be_independent(): void
    {
        // GIVEN: Multiple instances
        $dto1 = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user1@example.com',
            name: 'User 1',
            token: 'token1',
            refreshToken: 'refresh1',
            expireTime: 3600,
        );

        $dto2 = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user2@example.com',
            name: 'User 2',
            token: 'token2',
            refreshToken: 'refresh2',
            expireTime: 7200,
        );

        // THEN: Should not interfere with each other
        $data1 = $dto1->jsonSerialize();
        $data2 = $dto2->jsonSerialize();

        $this->assertEquals('user1@example.com', $data1['email']);
        $this->assertEquals('user2@example.com', $data2['email']);
        $this->assertNotEquals($data1['email'], $data2['email']);
    }

    /** @test */
    #[Test]
    public function should_handle_consecutive_json_serialize_calls(): void
    {
        // GIVEN: LoginResponseDTO instance
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: 'user@example.com',
            name: 'John Doe',
            token: 'token123',
            refreshToken: 'refresh123',
            expireTime: 3600,
        );

        // WHEN: Calling jsonSerialize multiple times
        $data1 = $dto->jsonSerialize();
        $data2 = $dto->jsonSerialize();
        $data3 = $dto->jsonSerialize();

        // THEN: Should return consistent results
        $this->assertEquals($data1, $data2);
        $this->assertEquals($data2, $data3);
    }

    /** @test */
    #[Test]
    public function should_handle_null_byte_attempt_in_fields(): void
    {
        // GIVEN: Null byte injection attempt
        $dto = new LoginResponseDTO(
            uuid: Uuid::uuid7()->toString(),
            email: "user@example.com\0admin",
            name: "John\0Doe",
            token: "token\0injection",
            refreshToken: "refresh\0token",
            expireTime: 3600,
        );

        // THEN: Should serialize with null bytes
        $data = $dto->jsonSerialize();
        $this->assertStringContainsString("\0", $data['email']);
        $this->assertStringContainsString("\0", $data['name']);
    }
}
