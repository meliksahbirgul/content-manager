<?php

declare(strict_types=1);

namespace Tests\Unit\Users\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Domain\Entity\UserTokenEntity;

class UserTokenEntityTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithValidData(): void
    {
        // GIVEN: Valid token data
        $accessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.access';
        $refreshToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.refresh';
        $expiresAt = 1735689600; // 2025-01-01 00:00:00

        // WHEN: Creating UserTokenEntity
        $tokenEntity = new UserTokenEntity(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: $expiresAt,
        );

        // THEN: Should create instance with correct data
        $this->assertInstanceOf(UserTokenEntity::class, $tokenEntity);
        $this->assertEquals($accessToken, $tokenEntity->accessToken());
        $this->assertEquals($refreshToken, $tokenEntity->refreshToken());
        $this->assertEquals($expiresAt, $tokenEntity->expiresAt());
    }

    /** @test */
    #[Test]
    public function shouldReturnAccessTokenCorrectly(): void
    {
        // GIVEN: UserTokenEntity with specific access token
        $accessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.payload.signature';
        $tokenEntity = new UserTokenEntity(
            accessToken: $accessToken,
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should return the exact access token provided
        $this->assertEquals($accessToken, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnRefreshTokenCorrectly(): void
    {
        // GIVEN: UserTokenEntity with specific refresh token
        $refreshToken = 'refresh_token_value_123456789';
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: $refreshToken,
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should return the exact refresh token provided
        $this->assertEquals($refreshToken, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function shouldReturnExpiresAtCorrectly(): void
    {
        // GIVEN: UserTokenEntity with specific expiration timestamp
        $expiresAt = 1735689600;
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: $expiresAt,
        );

        // WHEN: Calling expiresAt()
        $result = $tokenEntity->expiresAt();

        // THEN: Should return the exact expiration timestamp
        $this->assertEquals($expiresAt, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyAccessToken(): void
    {
        // GIVEN: UserTokenEntity with empty access token
        $tokenEntity = new UserTokenEntity(
            accessToken: '',
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should return empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleEmptyRefreshToken(): void
    {
        // GIVEN: UserTokenEntity with empty refresh token
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: '',
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should return empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleZeroExpiresAt(): void
    {
        // GIVEN: UserTokenEntity with zero expiration
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: 0,
        );

        // WHEN: Calling expiresAt()
        $result = $tokenEntity->expiresAt();

        // THEN: Should return zero
        $this->assertEquals(0, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleNegativeExpiresAt(): void
    {
        // GIVEN: UserTokenEntity with negative expiration (past timestamp)
        $expiresAt = -1735689600;
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: $expiresAt,
        );

        // WHEN: Calling expiresAt()
        $result = $tokenEntity->expiresAt();

        // THEN: Should return negative value
        $this->assertEquals($expiresAt, $result);
        $this->assertLessThan(0, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleVeryLargeExpiresAt(): void
    {
        // GIVEN: UserTokenEntity with very large expiration timestamp
        $expiresAt = PHP_INT_MAX;
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: $expiresAt,
        );

        // WHEN: Calling expiresAt()
        $result = $tokenEntity->expiresAt();

        // THEN: Should return very large value
        $this->assertEquals($expiresAt, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleVeryLongAccessToken(): void
    {
        // GIVEN: Very long access token (10000+ characters)
        $longToken = str_repeat('A', 10000);
        $tokenEntity = new UserTokenEntity(
            accessToken: $longToken,
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should preserve entire long token
        $this->assertEquals($longToken, $result);
        $this->assertStringStartsWith('AAA', $result);
        $this->assertStringEndsWith('AAA', $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleVeryLongRefreshToken(): void
    {
        // GIVEN: Very long refresh token
        $longToken = str_repeat('B', 10000);
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: $longToken,
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should preserve entire long token
        $this->assertEquals($longToken, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleSpecialCharactersInAccessToken(): void
    {
        // GIVEN: Access token with special characters
        $token = '!@#$%^&*()-_=+[]{}|;:,.<>?/~`';
        $tokenEntity = new UserTokenEntity(
            accessToken: $token,
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should preserve all special characters
        $this->assertEquals($token, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleSpecialCharactersInRefreshToken(): void
    {
        // GIVEN: Refresh token with special characters
        $token = '!@#$%^&*()-_=+[]{}|;:,.<>?/~`';
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: $token,
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should preserve all special characters
        $this->assertEquals($token, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleUnicodeInAccessToken(): void
    {
        // GIVEN: Access token with unicode characters
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.日本語.署名';
        $tokenEntity = new UserTokenEntity(
            accessToken: $token,
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should preserve unicode
        $this->assertEquals($token, $result);
        $this->assertStringContainsString('日本語', $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleUnicodeInRefreshToken(): void
    {
        // GIVEN: Refresh token with unicode characters
        $token = 'русский_токен_обновления';
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: $token,
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should preserve unicode
        $this->assertEquals($token, $result);
        $this->assertStringContainsString('русский', $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleNullByteInAccessToken(): void
    {
        // GIVEN: Access token with null byte (injection attempt)
        $token = "access\0token";
        $tokenEntity = new UserTokenEntity(
            accessToken: $token,
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should preserve null byte
        $this->assertEquals($token, $result);
        $this->assertStringContainsString("\0", $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleNullByteInRefreshToken(): void
    {
        // GIVEN: Refresh token with null byte
        $token = "refresh\0token";
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: $token,
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should preserve null byte
        $this->assertEquals($token, $result);
        $this->assertStringContainsString("\0", $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleWhitespaceInAccessToken(): void
    {
        // GIVEN: Access token with leading/trailing whitespace
        $token = '  access_token_value  ';
        $tokenEntity = new UserTokenEntity(
            accessToken: $token,
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should preserve whitespace
        $this->assertEquals($token, $result);
        $this->assertStringStartsWith('  ', $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleWhitespaceInRefreshToken(): void
    {
        // GIVEN: Refresh token with tabs and newlines
        $token = "\t\nrefresh_token\t\n";
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: $token,
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should preserve whitespace characters
        $this->assertEquals($token, $result);
        $this->assertStringContainsString("\t", $result);
        $this->assertStringContainsString("\n", $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleSQLInjectionAttemptInAccessToken(): void
    {
        // GIVEN: Access token containing SQL injection attempt
        $token = "'; DROP TABLE users; --";
        $tokenEntity = new UserTokenEntity(
            accessToken: $token,
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should preserve string as-is (entity doesn't sanitize)
        $this->assertEquals($token, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleXSSAttemptInRefreshToken(): void
    {
        // GIVEN: Refresh token containing XSS attempt
        $token = '<script>alert("xss")</script>';
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: $token,
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should preserve string as-is (entity doesn't sanitize)
        $this->assertEquals($token, $result);
    }

    /** @test */
    #[Test]
    public function shouldMaintainDataImmutabilityAcrossMultipleCalls(): void
    {
        // GIVEN: UserTokenEntity instance
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling methods multiple times
        $accessToken1 = $tokenEntity->accessToken();
        $refreshToken1 = $tokenEntity->refreshToken();
        $expiresAt1 = $tokenEntity->expiresAt();

        $accessToken2 = $tokenEntity->accessToken();
        $refreshToken2 = $tokenEntity->refreshToken();
        $expiresAt2 = $tokenEntity->expiresAt();

        // THEN: Values should remain consistent
        $this->assertEquals($accessToken1, $accessToken2);
        $this->assertEquals($refreshToken1, $refreshToken2);
        $this->assertEquals($expiresAt1, $expiresAt2);
    }

    /** @test */
    #[Test]
    public function shouldNotAffectOtherInstancesWhenCreatingNew(): void
    {
        // GIVEN: Multiple UserTokenEntity instances
        $token1 = new UserTokenEntity(
            accessToken: 'access_token_1',
            refreshToken: 'refresh_token_1',
            expiresAt: 1735689600,
        );

        $token2 = new UserTokenEntity(
            accessToken: 'access_token_2',
            refreshToken: 'refresh_token_2',
            expiresAt: 1735776000,
        );

        // WHEN: Getting values from both
        $accessToken1 = $token1->accessToken();
        $accessToken2 = $token2->accessToken();

        // THEN: Each instance maintains its own data
        $this->assertEquals('access_token_1', $accessToken1);
        $this->assertEquals('access_token_2', $accessToken2);
        $this->assertNotEquals($accessToken1, $accessToken2);
    }

    /** @test */
    #[Test]
    public function shouldHandleJWTFormatAccessToken(): void
    {
        // GIVEN: Properly formatted JWT access token
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';
        $tokenEntity = new UserTokenEntity(
            accessToken: $token,
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Calling accessToken()
        $result = $tokenEntity->accessToken();

        // THEN: Should preserve JWT format
        $this->assertEquals($token, $result);
        $this->assertStringContainsString('.', $result);
        $this->assertEquals(2, substr_count($result, '.'));
    }

    /** @test */
    #[Test]
    public function shouldHandleBearerTokenFormatRefreshToken(): void
    {
        // GIVEN: Bearer token format (token without "Bearer " prefix)
        $token = '0123456789abcdef0123456789abcdef0123456789';
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: $token,
            expiresAt: 1735689600,
        );

        // WHEN: Calling refreshToken()
        $result = $tokenEntity->refreshToken();

        // THEN: Should preserve token as-is
        $this->assertEquals($token, $result);
        $this->assertStringNotContainsString('Bearer', $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleSingleCharacterTokens(): void
    {
        // GIVEN: Single character tokens
        $tokenEntity = new UserTokenEntity(
            accessToken: 'A',
            refreshToken: 'B',
            expiresAt: 1735689600,
        );

        // WHEN: Getting token values
        $accessToken = $tokenEntity->accessToken();
        $refreshToken = $tokenEntity->refreshToken();

        // THEN: Should return single characters
        $this->assertEquals('A', $accessToken);
        $this->assertEquals('B', $refreshToken);
    }

    /** @test */
    #[Test]
    public function shouldPreserveExactCasing(): void
    {
        // GIVEN: Mixed case tokens
        $accessToken = 'eYjAlGcI.pAyLoaD.sIgNaTuRe';
        $refreshToken = 'RefReSh_ToKeN_VaLuE';

        $tokenEntity = new UserTokenEntity(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: 1735689600,
        );

        // WHEN: Getting values
        $resultAccessToken = $tokenEntity->accessToken();
        $resultRefreshToken = $tokenEntity->refreshToken();

        // THEN: Should preserve exact casing
        $this->assertEquals($accessToken, $resultAccessToken);
        $this->assertEquals($refreshToken, $resultRefreshToken);
    }

    /** @test */
    #[Test]
    public function shouldHandleNumericStringTokens(): void
    {
        // GIVEN: Numeric string tokens
        $accessToken = '123456789012345678901234567890';
        $refreshToken = '987654321098765432109876543210';

        $tokenEntity = new UserTokenEntity(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: 1735689600,
        );

        // WHEN: Getting values
        $resultAccessToken = $tokenEntity->accessToken();
        $resultRefreshToken = $tokenEntity->refreshToken();

        // THEN: Should preserve numeric strings
        $this->assertEquals($accessToken, $resultAccessToken);
        $this->assertEquals($refreshToken, $resultRefreshToken);
        $this->assertIsString($resultAccessToken);
        $this->assertIsString($resultRefreshToken);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectTypes(): void
    {
        // GIVEN: UserTokenEntity instance
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: 1735689600,
        );

        // WHEN: Getting all values
        $accessToken = $tokenEntity->accessToken();
        $refreshToken = $tokenEntity->refreshToken();
        $expiresAt = $tokenEntity->expiresAt();

        // THEN: Should return correct types
        $this->assertIsString($accessToken);
        $this->assertIsString($refreshToken);
        $this->assertIsInt($expiresAt);
    }

    /** @test */
    #[Test]
    public function shouldHandleNewlineCharactersInTokens(): void
    {
        // GIVEN: Tokens with newline characters
        $accessToken = "access\ntoken\nvalue";
        $refreshToken = "refresh\ntoken\nvalue";

        $tokenEntity = new UserTokenEntity(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: 1735689600,
        );

        // WHEN: Getting values
        $resultAccessToken = $tokenEntity->accessToken();
        $resultRefreshToken = $tokenEntity->refreshToken();

        // THEN: Should preserve newlines
        $this->assertStringContainsString("\n", $resultAccessToken);
        $this->assertStringContainsString("\n", $resultRefreshToken);
    }

    /** @test */
    #[Test]
    public function shouldHandleTabCharactersInTokens(): void
    {
        // GIVEN: Tokens with tab characters
        $accessToken = "access\ttoken\tvalue";
        $refreshToken = "refresh\ttoken\tvalue";

        $tokenEntity = new UserTokenEntity(
            accessToken: $accessToken,
            refreshToken: $refreshToken,
            expiresAt: 1735689600,
        );

        // WHEN: Getting values
        $resultAccessToken = $tokenEntity->accessToken();
        $resultRefreshToken = $tokenEntity->refreshToken();

        // THEN: Should preserve tabs
        $this->assertStringContainsString("\t", $resultAccessToken);
        $this->assertStringContainsString("\t", $resultRefreshToken);
    }

    /** @test */
    #[Test]
    public function shouldHandleSmallestPossibleExpiresAt(): void
    {
        // GIVEN: UserTokenEntity with smallest possible integer
        $expiresAt = PHP_INT_MIN;
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: $expiresAt,
        );

        // WHEN: Calling expiresAt()
        $result = $tokenEntity->expiresAt();

        // THEN: Should return smallest integer value
        $this->assertEquals($expiresAt, $result);
        $this->assertIsInt($result);
    }

    /** @test */
    #[Test]
    public function shouldHandleCurrentTimestamp(): void
    {
        // GIVEN: UserTokenEntity with current timestamp
        $currentTime = time();
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: $currentTime,
        );

        // WHEN: Calling expiresAt()
        $result = $tokenEntity->expiresAt();

        // THEN: Should return current timestamp
        $this->assertEquals($currentTime, $result);
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleFutureTimestamp(): void
    {
        // GIVEN: UserTokenEntity with future timestamp (1 year from now)
        $futureTime = time() + (365 * 24 * 60 * 60);
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: $futureTime,
        );

        // WHEN: Calling expiresAt()
        $result = $tokenEntity->expiresAt();

        // THEN: Should return future timestamp
        $this->assertEquals($futureTime, $result);
        $this->assertGreaterThan(time(), $result);
    }

    /** @test */
    #[Test]
    public function shouldHandlePastTimestamp(): void
    {
        // GIVEN: UserTokenEntity with past timestamp (1 year ago)
        $pastTime = time() - (365 * 24 * 60 * 60);
        $tokenEntity = new UserTokenEntity(
            accessToken: 'access_token',
            refreshToken: 'refresh_token',
            expiresAt: $pastTime,
        );

        // WHEN: Calling expiresAt()
        $result = $tokenEntity->expiresAt();

        // THEN: Should return past timestamp
        $this->assertEquals($pastTime, $result);
        $this->assertLessThan(time(), $result);
    }
}
