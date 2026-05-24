<?php

declare(strict_types=1);

namespace Tests\Unit\Users\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\RefreshDTO;

class RefreshDTOSadPathTest extends TestCase
{
    /** @test */
    #[Test]
    public function should_handle_empty_string_refresh_token(): void
    {
        // GIVEN: Empty string token
        $dto = new RefreshDTO(refreshToken: '');

        // THEN: Should create DTO
        $this->assertEquals('', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_whitespace_only_token(): void
    {
        // GIVEN: Whitespace-only token
        $dto = new RefreshDTO(refreshToken: '   ');

        // THEN: Should create DTO
        $this->assertEquals('   ', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_tabs_in_token(): void
    {
        // GIVEN: Token with tabs
        $dto = new RefreshDTO(refreshToken: "\t\t\t");

        // THEN: Should create DTO
        $this->assertEquals("\t\t\t", $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_newlines_in_token(): void
    {
        // GIVEN: Token with newlines
        $dto = new RefreshDTO(refreshToken: "token\nwith\nnewlines");

        // THEN: Should create DTO
        $this->assertEquals("token\nwith\nnewlines", $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_very_long_token(): void
    {
        // GIVEN: Extremely long token
        $longToken = str_repeat('a', 10000);
        $dto = new RefreshDTO(refreshToken: $longToken);

        // THEN: Should create DTO
        $this->assertEquals($longToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_special_characters_in_token(): void
    {
        // GIVEN: Token with special characters
        $token = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_null_byte_in_token(): void
    {
        // GIVEN: Token with null byte
        $token = "token\0injection";
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO
        $this->assertStringContainsString("\0", $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_unicode_in_token(): void
    {
        // GIVEN: Token with unicode characters
        $token = 'token_トークン_🔐';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO
        $this->assertStringContainsString('トークン', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_numeric_token(): void
    {
        // GIVEN: Pure numeric token
        $dto = new RefreshDTO(refreshToken: '123456789');

        // THEN: Should create DTO
        $this->assertEquals('123456789', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_single_character_token(): void
    {
        // GIVEN: Single character token
        $dto = new RefreshDTO(refreshToken: 'a');

        // THEN: Should create DTO
        $this->assertEquals('a', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_be_readonly(): void
    {
        // GIVEN: RefreshDTO instance
        $dto = new RefreshDTO(refreshToken: 'mytoken');

        // THEN: Should not be able to modify via reflection
        $reflection = new \ReflectionClass($dto);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            // Check if property is readonly (PHP 8.1+)
            if (method_exists($property, 'isReadonly')) {
                $this->assertTrue($property->isReadonly(), 'Property should be readonly');
            }
        }
    }

    /** @test */
    #[Test]
    public function should_preserve_token_exactly(): void
    {
        // GIVEN: Token with mixed case and special formatting
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should preserve exactly
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_multiple_instances_independently(): void
    {
        // GIVEN: Multiple RefreshDTO instances
        $dto1 = new RefreshDTO(refreshToken: 'token1');
        $dto2 = new RefreshDTO(refreshToken: 'token2');
        $dto3 = new RefreshDTO(refreshToken: 'token3');

        // THEN: Should not interfere with each other
        $this->assertEquals('token1', $dto1->refreshToken());
        $this->assertEquals('token2', $dto2->refreshToken());
        $this->assertEquals('token3', $dto3->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_html_injection_attempt(): void
    {
        // GIVEN: Token with HTML/XSS attempt
        $token = '<script>alert("xss")</script>';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO (sanitization not responsibility of DTO)
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_sql_injection_attempt(): void
    {
        // GIVEN: Token with SQL injection attempt
        $token = "'; DROP TABLE users; --";
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO (sanitization not responsibility of DTO)
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_line_breaks_with_different_formats(): void
    {
        // GIVEN: Token with various line break formats
        $tokenLF = "token\nwith\nlf";
        $tokenCR = "token\rwith\rcr";
        $tokenCRLF = "token\r\nwith\r\ncrlf";

        // THEN: Should preserve all formats
        $dto1 = new RefreshDTO(refreshToken: $tokenLF);
        $dto2 = new RefreshDTO(refreshToken: $tokenCR);
        $dto3 = new RefreshDTO(refreshToken: $tokenCRLF);

        $this->assertEquals($tokenLF, $dto1->refreshToken());
        $this->assertEquals($tokenCR, $dto2->refreshToken());
        $this->assertEquals($tokenCRLF, $dto3->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_zero_length_string(): void
    {
        // GIVEN: Zero-length string
        $dto = new RefreshDTO(refreshToken: '');

        // THEN: Should create DTO
        $this->assertEmpty($dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function should_handle_leading_and_trailing_whitespace(): void
    {
        // GIVEN: Token with leading and trailing whitespace
        $token = '   token_with_spaces   ';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should preserve whitespace
        $this->assertEquals('   token_with_spaces   ', $dto->refreshToken());
    }
}
