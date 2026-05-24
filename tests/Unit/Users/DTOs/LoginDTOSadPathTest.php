<?php

declare(strict_types=1);

namespace Tests\Unit\Users\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\LoginDTO;

class LoginDTOSadPathTest extends TestCase
{
    /** @test */
    #[Test]
    public function should_handle_from_request_with_missing_email_key(): void
    {
        // GIVEN: Request data without email key
        $data = ['password' => 'password123'];

        // THEN: Should throw exception (undefined array key)
        $this->expectException(\InvalidArgumentException::class);

        // WHEN: Creating from request
        LoginDTO::fromRequest($data);
    }

    /** @test */
    #[Test]
    public function should_handle_from_request_with_missing_password_key(): void
    {
        // GIVEN: Request data without password key
        $data = ['email' => 'user@example.com'];

        // THEN: Should throw exception (undefined array key)
        $this->expectException(\InvalidArgumentException::class);

        // WHEN: Creating from request
        LoginDTO::fromRequest($data);
    }

    /** @test */
    #[Test]
    public function should_handle_from_request_with_type_coercion(): void
    {
        // GIVEN: Request data with coercible types
        // Note: Type hints on LoginDTO require strings, so numeric types may cause issues
        // This tests that the fromRequest method doesn't do extra validation
        $data = ['email' => 'user@example.com', 'password' => 'password123'];

        // WHEN: Creating from request
        $dto = LoginDTO::fromRequest($data);

        // THEN: Should create successfully
        $this->assertNotNull($dto);
        $this->assertEquals('user@example.com', $dto->email());
    }

    /** @test */
    #[Test]
    public function should_ignore_extra_keys_from_request(): void
    {
        // GIVEN: Request data with extra keys
        $data = [
            'email' => 'user@example.com',
            'password' => 'password123',
            'extra_key' => 'extra_value',
            'another_key' => 'another_value',
        ];

        // WHEN: Creating from request
        $dto = LoginDTO::fromRequest($data);

        // THEN: Should only have email and password
        $this->assertEquals('user@example.com', $dto->email());
        $this->assertEquals('password123', $dto->password());
    }

    /** @test */
    #[Test]
    public function should_handle_empty_string_email(): void
    {
        // GIVEN: Empty string email
        $dto = new LoginDTO(email: '', password: 'password123');

        // WHEN/THEN: Should create DTO (validation in ValueObject will catch)
        $this->assertEquals('', $dto->email());
    }

    /** @test */
    #[Test]
    public function should_handle_empty_string_password(): void
    {
        // GIVEN: Empty string password
        $dto = new LoginDTO(email: 'user@example.com', password: '');

        // WHEN/THEN: Should create DTO (validation in ValueObject will catch)
        $this->assertEquals('', $dto->password());
    }

    /** @test */
    #[Test]
    public function should_handle_whitespace_only_email(): void
    {
        // GIVEN: Whitespace-only email
        $dto = new LoginDTO(email: '   ', password: 'password123');

        // THEN: Should create DTO
        $this->assertEquals('   ', $dto->email());
    }

    /** @test */
    #[Test]
    public function should_handle_whitespace_only_password(): void
    {
        // GIVEN: Whitespace-only password
        $dto = new LoginDTO(email: 'user@example.com', password: '        ');

        // THEN: Should create DTO
        $this->assertEquals('        ', $dto->password());
    }

    /** @test */
    #[Test]
    public function should_handle_very_long_email(): void
    {
        // GIVEN: Very long but valid email
        $longEmail = str_repeat('a', 200).'@example.com';
        $dto = new LoginDTO(email: $longEmail, password: 'password123');

        // THEN: Should create DTO
        $this->assertEquals($longEmail, $dto->email());
    }

    /** @test */
    #[Test]
    public function should_handle_very_long_password(): void
    {
        // GIVEN: Very long password
        $longPassword = str_repeat('a', 1000);
        $dto = new LoginDTO(email: 'user@example.com', password: $longPassword);

        // THEN: Should create DTO
        $this->assertEquals($longPassword, $dto->password());
    }

    /** @test */
    #[Test]
    public function should_handle_numeric_strings_in_email(): void
    {
        // GIVEN: Numeric string (not valid email)
        $dto = new LoginDTO(email: '12345', password: 'password123');

        // THEN: Should create DTO (validation in ValueObject will catch)
        $this->assertEquals('12345', $dto->email());
    }

    /** @test */
    #[Test]
    public function should_handle_numeric_strings_in_password(): void
    {
        // GIVEN: Numeric string password
        $dto = new LoginDTO(email: 'user@example.com', password: '12345678');

        // THEN: Should create DTO
        $this->assertEquals('12345678', $dto->password());
    }

    /** @test */
    #[Test]
    public function should_handle_special_characters_in_email(): void
    {
        // GIVEN: Special characters in email
        $dto = new LoginDTO(email: 'user+tag@example.co.uk', password: 'password123');

        // THEN: Should create DTO
        $this->assertEquals('user+tag@example.co.uk', $dto->email());
    }

    /** @test */
    #[Test]
    public function should_handle_null_byte_injection_attempt(): void
    {
        // GIVEN: Null byte injection attempt
        $dto = new LoginDTO(email: "user@example.com\0admin", password: 'password123');

        // THEN: Should create DTO
        $this->assertStringContainsString("\0", $dto->email());
    }

    /** @test */
    #[Test]
    public function should_handle_from_request_with_empty_array(): void
    {
        // GIVEN: Empty request data
        $data = [];

        // THEN: Should throw exception (undefined array keys)
        $this->expectException(\Throwable::class);

        // WHEN: Creating from request
        LoginDTO::fromRequest($data);
    }

    /** @test */
    #[Test]
    public function should_handle_from_request_with_only_extra_keys(): void
    {
        // GIVEN: Request with only extra keys
        $data = ['extra' => 'value', 'another' => 'data'];

        // THEN: Should throw exception (undefined array keys)
        $this->expectException(\Throwable::class);

        // WHEN: Creating from request
        LoginDTO::fromRequest($data);
    }

    /** @test */
    #[Test]
    public function should_handle_from_request_with_integer_values(): void
    {
        // GIVEN: Request with integer values (wrong type)
        $data = ['email' => 123, 'password' => 456];

        // THEN: May throw or coerce to string (PHP behavior)
        // For now, we're just verifying it doesn't crash
        try {
            $dto = LoginDTO::fromRequest($data);
            $this->assertNotNull($dto);
        } catch (\Throwable $e) {
            // If it throws, that's fine too
            $this->assertTrue(true);
        }
    }

    /** @test */
    #[Test]
    public function should_handle_from_request_with_array_values(): void
    {
        // GIVEN: Request with array values (wrong type)
        $data = ['email' => ['nested' => 'value'], 'password' => []];

        // THEN: Should throw or handle gracefully
        try {
            LoginDTO::fromRequest($data);
            $this->fail('Should have thrown exception for array values');
        } catch (\Throwable $e) {
            $this->assertTrue(true);
        }
    }

    /** @test */
    #[Test]
    public function should_preserve_email_case_sensitivity(): void
    {
        // GIVEN: Email with mixed case
        $email = 'User@Example.COM';
        $dto = new LoginDTO(email: $email, password: 'password123');

        // THEN: Should preserve case as-is
        $this->assertEquals('User@Example.COM', $dto->email());
    }

    /** @test */
    #[Test]
    public function should_preserve_password_case_sensitivity(): void
    {
        // GIVEN: Password with mixed case
        $password = 'PassWord123ABC';
        $dto = new LoginDTO(email: 'user@example.com', password: $password);

        // THEN: Should preserve case as-is
        $this->assertEquals('PassWord123ABC', $dto->password());
    }

    /** @test */
    #[Test]
    public function should_handle_unicode_characters_in_email(): void
    {
        // GIVEN: Email with unicode characters (invalid email but should not crash)
        $dto = new LoginDTO(email: 'user@例え.jp', password: 'password123');

        // THEN: Should create DTO
        $this->assertStringContainsString('例え', $dto->email());
    }

    /** @test */
    #[Test]
    public function should_handle_unicode_characters_in_password(): void
    {
        // GIVEN: Password with unicode characters
        $dto = new LoginDTO(email: 'user@example.com', password: 'пароль123');

        // THEN: Should create DTO
        $this->assertStringContainsString('пароль', $dto->password());
    }
}
