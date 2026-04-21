<?php

declare(strict_types=1);

namespace Tests\Unit\Users\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\RefreshDTO;

class RefreshDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithValidToken(): void
    {
        // GIVEN: Valid refresh token
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...';

        // WHEN: Creating RefreshDTO
        $dto = new RefreshDTO(refreshToken: $token);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(RefreshDTO::class, $dto);
        $this->assertEquals($token, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectRefreshToken(): void
    {
        // GIVEN: RefreshDTO with specific token
        $token = 'refresh_token_abc123xyz';
        $dto = new RefreshDTO(refreshToken: $token);

        // WHEN: Calling refreshToken()
        $result = $dto->refreshToken();

        // THEN: Should return correct token
        $this->assertEquals($token, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleJWTTokenFormat(): void
    {
        // GIVEN: JWT format token
        $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

        // WHEN: Creating RefreshDTO
        $dto = new RefreshDTO(refreshToken: $jwtToken);

        // THEN: Should handle JWT token correctly
        $this->assertEquals($jwtToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleSimpleTokenFormat(): void
    {
        // GIVEN: Simple token format
        $simpleToken = 'simple_token_123456';

        // WHEN: Creating RefreshDTO
        $dto = new RefreshDTO(refreshToken: $simpleToken);

        // THEN: Should handle simple token correctly
        $this->assertEquals($simpleToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleVeryLongTokens(): void
    {
        // GIVEN: Very long token
        $longToken = str_repeat('a', 10000);

        // WHEN: Creating RefreshDTO
        $dto = new RefreshDTO(refreshToken: $longToken);

        // THEN: Should handle long token correctly
        $this->assertEquals($longToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleTokensWithSpecialCharacters(): void
    {
        // GIVEN: Token with special characters
        $specialTokens = [
            'token-with-dashes',
            'token_with_underscores',
            'token.with.dots',
            'token+with+plus',
            'token/with/slashes',
            'token=with=equals',
        ];

        foreach ($specialTokens as $token) {
            // WHEN: Creating RefreshDTO
            $dto = new RefreshDTO(refreshToken: $token);

            // THEN: Should handle special characters correctly
            $this->assertEquals($token, $dto->refreshToken());
        }
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyToken(): void
    {
        // GIVEN: Empty token (validation happens in RefreshUser value object)
        $emptyToken = '';

        // WHEN: Creating RefreshDTO (no validation at DTO level)
        $dto = new RefreshDTO(refreshToken: $emptyToken);

        // THEN: Should create instance (validation in value object)
        $this->assertEquals($emptyToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldBeReadonly(): void
    {
        // GIVEN: RefreshDTO instance
        $dto = new RefreshDTO(refreshToken: 'token123');

        // THEN: Should be readonly
        $this->assertInstanceOf(RefreshDTO::class, $dto);
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadonly(), 'RefreshDTO should be readonly');
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleInstances(): void
    {
        // GIVEN: Multiple RefreshDTO instances
        $dto1 = new RefreshDTO(refreshToken: 'token_1');
        $dto2 = new RefreshDTO(refreshToken: 'token_2');
        $dto3 = new RefreshDTO(refreshToken: 'token_3');

        // WHEN: Accessing their tokens
        $token1 = $dto1->refreshToken();
        $token2 = $dto2->refreshToken();
        $token3 = $dto3->refreshToken();

        // THEN: Each instance should have different tokens
        $this->assertNotEquals($token1, $token2);
        $this->assertNotEquals($token2, $token3);
        $this->assertNotEquals($token1, $token3);
        $this->assertEquals('token_1', $token1);
        $this->assertEquals('token_2', $token2);
        $this->assertEquals('token_3', $token3);
    }

    /** @test */
    #[Test]
    public function shouldHandleTokensWithWhitespace(): void
    {
        // GIVEN: Token with whitespace (preserved as-is)
        $tokenWithSpaces = '  token with spaces  ';

        // WHEN: Creating RefreshDTO
        $dto = new RefreshDTO(refreshToken: $tokenWithSpaces);

        // THEN: Should preserve whitespace
        $this->assertEquals($tokenWithSpaces, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleUnicodeCharactersInToken(): void
    {
        // GIVEN: Token with unicode characters
        $unicodeToken = 'token_with_émojis_🔐_and_ñ';

        // WHEN: Creating RefreshDTO
        $dto = new RefreshDTO(refreshToken: $unicodeToken);

        // THEN: Should preserve unicode characters
        $this->assertEquals($unicodeToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleTokensWithNewlines(): void
    {
        // GIVEN: Token with newlines
        $tokenWithNewlines = "token\nwith\nmultiple\nlines";

        // WHEN: Creating RefreshDTO
        $dto = new RefreshDTO(refreshToken: $tokenWithNewlines);

        // THEN: Should preserve newlines
        $this->assertEquals($tokenWithNewlines, $dto->refreshToken());
    }
}
