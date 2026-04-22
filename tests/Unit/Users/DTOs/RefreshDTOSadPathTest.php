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
    public function shouldHandleEmptyStringRefreshToken(): void
    {
        // GIVEN: Empty string token
        $dto = new RefreshDTO(refreshToken: '');

        // THEN: Should create DTO
        $this->assertEquals('', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleWhitespaceOnlyToken(): void
    {
        // GIVEN: Whitespace-only token
        $dto = new RefreshDTO(refreshToken: '   ');

        // THEN: Should create DTO
        $this->assertEquals('   ', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleTabsInToken(): void
    {
        // GIVEN: Token with tabs
        $dto = new RefreshDTO(refreshToken: "\t\t\t");

        // THEN: Should create DTO
        $this->assertEquals("\t\t\t", $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleNewlinesInToken(): void
    {
        // GIVEN: Token with newlines
        $dto = new RefreshDTO(refreshToken: "token\nwith\nnewlines");

        // THEN: Should create DTO
        $this->assertEquals("token\nwith\nnewlines", $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleVeryLongToken(): void
    {
        // GIVEN: Extremely long token
        $longToken = str_repeat('a', 10000);
        $dto = new RefreshDTO(refreshToken: $longToken);

        // THEN: Should create DTO
        $this->assertEquals($longToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleSpecialCharactersInToken(): void
    {
        // GIVEN: Token with special characters
        $token = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleNullByteInToken(): void
    {
        // GIVEN: Token with null byte
        $token = "token\0injection";
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO
        $this->assertStringContainsString("\0", $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleUnicodeInToken(): void
    {
        // GIVEN: Token with unicode characters
        $token = 'token_トークン_🔐';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO
        $this->assertStringContainsString('トークン', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleNumericToken(): void
    {
        // GIVEN: Pure numeric token
        $dto = new RefreshDTO(refreshToken: '123456789');

        // THEN: Should create DTO
        $this->assertEquals('123456789', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleSingleCharacterToken(): void
    {
        // GIVEN: Single character token
        $dto = new RefreshDTO(refreshToken: 'a');

        // THEN: Should create DTO
        $this->assertEquals('a', $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldBeReadonly(): void
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
    public function shouldPreserveTokenExactly(): void
    {
        // GIVEN: Token with mixed case and special formatting
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should preserve exactly
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleInstancesIndependently(): void
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
    public function shouldHandleHtmlInjectionAttempt(): void
    {
        // GIVEN: Token with HTML/XSS attempt
        $token = '<script>alert("xss")</script>';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO (sanitization not responsibility of DTO)
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleSqlInjectionAttempt(): void
    {
        // GIVEN: Token with SQL injection attempt
        $token = "'; DROP TABLE users; --";
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create DTO (sanitization not responsibility of DTO)
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleLineBreaksWithDifferentFormats(): void
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
    public function shouldHandleZeroLengthString(): void
    {
        // GIVEN: Zero-length string
        $dto = new RefreshDTO(refreshToken: '');

        // THEN: Should create DTO
        $this->assertEmpty($dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleLeadingAndTrailingWhitespace(): void
    {
        // GIVEN: Token with leading and trailing whitespace
        $token = '   token_with_spaces   ';
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should preserve whitespace
        $this->assertEquals('   token_with_spaces   ', $dto->refreshToken());
    }
}
