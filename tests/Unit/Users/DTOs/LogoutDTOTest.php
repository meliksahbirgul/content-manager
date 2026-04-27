<?php

declare(strict_types=1);

namespace Tests\Unit\Users\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\LogoutDTO;

class LogoutDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithAccessTokenOnly(): void
    {
        // GIVEN: Valid access token
        $accessToken = 'access_token_12345';

        // WHEN: Creating LogoutDTO
        $dto = new LogoutDTO(accessToken: $accessToken);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(LogoutDTO::class, $dto);
        $this->assertEquals($accessToken, $dto->accessToken());
        $this->assertNull($dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldCreateInstanceWithAccessTokenAndRefreshToken(): void
    {
        // GIVEN: Valid access token and refresh token
        $accessToken = 'access_token_12345';
        $refreshToken = 'refresh_token_67890';

        // WHEN: Creating LogoutDTO
        $dto = new LogoutDTO(accessToken: $accessToken, refreshToken: $refreshToken);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(LogoutDTO::class, $dto);
        $this->assertEquals($accessToken, $dto->accessToken());
        $this->assertEquals($refreshToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectAccessToken(): void
    {
        // GIVEN: LogoutDTO with specific access token
        $accessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9';
        $dto = new LogoutDTO(accessToken: $accessToken);

        // WHEN: Calling accessToken()
        $result = $dto->accessToken();

        // THEN: Should return correct access token
        $this->assertEquals($accessToken, $result);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectRefreshToken(): void
    {
        // GIVEN: LogoutDTO with specific refresh token
        $refreshToken = 'refresh_token_xyz789';
        $dto = new LogoutDTO(
            accessToken: 'access_token_abc123',
            refreshToken: $refreshToken
        );

        // WHEN: Calling refreshToken()
        $result = $dto->refreshToken();

        // THEN: Should return correct refresh token
        $this->assertEquals($refreshToken, $result);
    }

    /** @test */
    #[Test]
    public function shouldReturnNullWhenRefreshTokenNotProvided(): void
    {
        // GIVEN: LogoutDTO without refresh token
        $dto = new LogoutDTO(accessToken: 'access_token_12345');

        // WHEN: Calling refreshToken()
        $result = $dto->refreshToken();

        // THEN: Should return null
        $this->assertNull($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleVariousAccessTokenFormats(): void
    {
        // GIVEN: Various access token formats
        $accessTokens = [
            'simple_token',
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
            'token_with_special_chars_!@#$%',
            'token_with_numbers_123456789',
            'very_long_token_' . str_repeat('a', 256),
        ];

        foreach ($accessTokens as $token) {
            // WHEN: Creating LogoutDTO
            $dto = new LogoutDTO(accessToken: $token);

            // THEN: Should handle token correctly
            $this->assertEquals($token, $dto->accessToken());
        }
    }

    /** @test */
    #[Test]
    public function shouldHandleVariousRefreshTokenFormats(): void
    {
        // GIVEN: Various refresh token formats
        $refreshTokens = [
            'simple_refresh_token',
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U',
            'refresh_token_with_special_chars_!@#$%',
            'refresh_token_with_numbers_987654321',
            'very_long_refresh_token_' . str_repeat('a', 256),
        ];

        foreach ($refreshTokens as $token) {
            // WHEN: Creating LogoutDTO
            $dto = new LogoutDTO(
                accessToken: 'access_token_12345',
                refreshToken: $token
            );

            // THEN: Should handle refresh token correctly
            $this->assertEquals($token, $dto->refreshToken());
        }
    }

    /** @test */
    #[Test]
    public function shouldBeReadonly(): void
    {
        // GIVEN: LogoutDTO instance
        $dto = new LogoutDTO(accessToken: 'access_token_12345');

        // THEN: Should be readonly
        $this->assertInstanceOf(LogoutDTO::class, $dto);
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadonly(), 'LogoutDTO should be readonly');
    }

    /** @test */
    #[Test]
    public function shouldHandleAccessTokenWithLeadingTrailingSpaces(): void
    {
        // GIVEN: Access token with whitespace
        $tokenWithSpaces = '  access_token_12345  ';

        // WHEN: Creating LogoutDTO (spaces are preserved by the class)
        $dto = new LogoutDTO(accessToken: $tokenWithSpaces);

        // THEN: Should preserve spaces as given
        $this->assertEquals($tokenWithSpaces, $dto->accessToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleRefreshTokenWithLeadingTrailingSpaces(): void
    {
        // GIVEN: Refresh token with whitespace
        $refreshTokenWithSpaces = '  refresh_token_67890  ';

        // WHEN: Creating LogoutDTO
        $dto = new LogoutDTO(
            accessToken: 'access_token_12345',
            refreshToken: $refreshTokenWithSpaces
        );

        // THEN: Should preserve spaces as given
        $this->assertEquals($refreshTokenWithSpaces, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyAccessToken(): void
    {
        // GIVEN: Empty access token string
        $emptyToken = '';

        // WHEN: Creating LogoutDTO (no validation at DTO level)
        $dto = new LogoutDTO(accessToken: $emptyToken);

        // THEN: Should create instance (validation happens elsewhere)
        $this->assertEquals($emptyToken, $dto->accessToken());
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyRefreshToken(): void
    {
        // GIVEN: Empty refresh token string
        $emptyToken = '';

        // WHEN: Creating LogoutDTO
        $dto = new LogoutDTO(
            accessToken: 'access_token_12345',
            refreshToken: $emptyToken
        );

        // THEN: Should create instance
        $this->assertEquals($emptyToken, $dto->refreshToken());
    }

    /** @test */
    #[Test]
    public function shouldMultipleInstancesBeIndependent(): void
    {
        // GIVEN: Two different LogoutDTO instances
        $dto1 = new LogoutDTO(
            accessToken: 'access_token_1',
            refreshToken: 'refresh_token_1'
        );
        $dto2 = new LogoutDTO(
            accessToken: 'access_token_2',
            refreshToken: 'refresh_token_2'
        );

        // WHEN: Accessing their data
        $accessToken1 = $dto1->accessToken();
        $refreshToken1 = $dto1->refreshToken();
        $accessToken2 = $dto2->accessToken();
        $refreshToken2 = $dto2->refreshToken();

        // THEN: Each instance should have independent data
        $this->assertNotEquals($accessToken1, $accessToken2);
        $this->assertNotEquals($refreshToken1, $refreshToken2);
        $this->assertEquals('access_token_1', $accessToken1);
        $this->assertEquals('refresh_token_1', $refreshToken1);
        $this->assertEquals('access_token_2', $accessToken2);
        $this->assertEquals('refresh_token_2', $refreshToken2);
    }

    /** @test */
    #[Test]
    public function shouldNullRefreshTokenRemainsNullAcrossInstances(): void
    {
        // GIVEN: Multiple instances with and without refresh token
        $dtoWithoutRefresh = new LogoutDTO(accessToken: 'access_token_1');
        $dtoWithRefresh = new LogoutDTO(
            accessToken: 'access_token_2',
            refreshToken: 'refresh_token_2'
        );

        // WHEN: Accessing refresh tokens
        $refreshToken1 = $dtoWithoutRefresh->refreshToken();
        $refreshToken2 = $dtoWithRefresh->refreshToken();

        // THEN: One should be null, other should have value
        $this->assertNull($refreshToken1);
        $this->assertNotNull($refreshToken2);
        $this->assertEquals('refresh_token_2', $refreshToken2);
    }
}
