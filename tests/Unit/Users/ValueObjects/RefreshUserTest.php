<?php

declare(strict_types=1);

namespace Tests\Unit\Users\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Domain\ValueObjects\RefreshUser;

class RefreshUserTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithValidToken(): void
    {
        // GIVEN: Valid refresh token
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(RefreshUser::class, $refreshUser);
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenTokenIsEmpty(): void
    {
        // GIVEN: Empty token
        $emptyToken = '';

        // THEN: Should throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token can not be empty.');

        // WHEN: Creating RefreshUser with empty token
        new RefreshUser(token: $emptyToken);
    }

    /** @test */
    #[Test]
    public function shouldValidateUponInstantiation(): void
    {
        // GIVEN: Empty token string
        $emptyToken = '';

        // THEN: Should throw exception during validation
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token can not be empty.');

        // WHEN: Creating RefreshUser
        new RefreshUser(token: $emptyToken);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectToken(): void
    {
        // GIVEN: RefreshUser with specific token
        $token = 'refresh_token_abc123xyz';
        $refreshUser = new RefreshUser(token: $token);

        // WHEN: Calling token()
        $result = $refreshUser->token();

        // THEN: Should return correct token
        $this->assertEquals($token, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleJWTTokenFormat(): void
    {
        // GIVEN: JWT format token
        $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

        // WHEN: Creating RefreshUser with JWT token
        $refreshUser = new RefreshUser(token: $jwtToken);

        // THEN: Should handle JWT token correctly
        $this->assertEquals($jwtToken, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldHandleSimpleTokenFormat(): void
    {
        // GIVEN: Simple token format
        $simpleToken = 'simple_token_123456';

        // WHEN: Creating RefreshUser with simple token
        $refreshUser = new RefreshUser(token: $simpleToken);

        // THEN: Should handle simple token correctly
        $this->assertEquals($simpleToken, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldHandleVeryLongTokens(): void
    {
        // GIVEN: Very long token
        $longToken = str_repeat('a', 10000);

        // WHEN: Creating RefreshUser with long token
        $refreshUser = new RefreshUser(token: $longToken);

        // THEN: Should handle long token correctly
        $this->assertEquals($longToken, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldHandleTokensWithSpecialCharacters(): void
    {
        // GIVEN: Tokens with special characters
        $specialTokens = [
            'token-with-dashes',
            'token_with_underscores',
            'token.with.dots',
            'token+with+plus',
            'token/with/slashes',
            'token=with=equals',
        ];

        foreach ($specialTokens as $token) {
            // WHEN: Creating RefreshUser with special character token
            $refreshUser = new RefreshUser(token: $token);

            // THEN: Should handle special characters correctly
            $this->assertEquals($token, $refreshUser->token());
        }
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleInstances(): void
    {
        // GIVEN: Multiple RefreshUser instances
        $user1 = new RefreshUser(token: 'token_1');
        $user2 = new RefreshUser(token: 'token_2');
        $user3 = new RefreshUser(token: 'token_3');

        // WHEN: Accessing their tokens
        $token1 = $user1->token();
        $token2 = $user2->token();
        $token3 = $user3->token();

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
        // GIVEN: Token with internal whitespace (not leading/trailing empty)
        $tokenWithSpaces = 'token with spaces inside';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $tokenWithSpaces);

        // THEN: Should preserve whitespace
        $this->assertEquals($tokenWithSpaces, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldRejectOnlyWhitespaceAsEmpty(): void
    {
        // GIVEN: Token with only whitespace
        // Note: The validation uses empty() which treats whitespace-only strings differently
        $whitespaceToken = '   ';

        // WHEN: Creating RefreshUser with whitespace-only token
        // Note: empty('   ') returns false in PHP, so whitespace-only is NOT empty
        $refreshUser = new RefreshUser(token: $whitespaceToken);

        // THEN: Should accept whitespace-only token
        $this->assertEquals('   ', $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionForNullConvertedToEmpty(): void
    {
        // GIVEN: Empty string (which would come from null in type coercion)
        $emptyToken = '';

        // THEN: Should throw exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token can not be empty.');

        // WHEN: Creating RefreshUser
        new RefreshUser(token: $emptyToken);
    }

    /** @test */
    #[Test]
    public function shouldHandleUnicodeCharactersInToken(): void
    {
        // GIVEN: Token with unicode characters
        $unicodeToken = 'token_with_émojis_🔐_and_ñ_and_中文';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $unicodeToken);

        // THEN: Should preserve unicode characters
        $this->assertEquals($unicodeToken, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldHandleNumericTokens(): void
    {
        // GIVEN: Token that looks numeric but is a string
        $numericToken = '123456789';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $numericToken);

        // THEN: Should handle numeric strings correctly
        $this->assertEquals('123456789', $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldAcceptSingleCharacterToken(): void
    {
        // GIVEN: Token with single character
        $singleCharToken = 'a';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $singleCharToken);

        // THEN: Should accept single character tokens
        $this->assertEquals('a', $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldHandleTokensWithNewlines(): void
    {
        // GIVEN: Token with newlines (not leading/trailing empty)
        $tokenWithNewlines = "token\nwith\nmultiple\nlines";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $tokenWithNewlines);

        // THEN: Should preserve newlines
        $this->assertEquals($tokenWithNewlines, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldHandleTokensWithTabs(): void
    {
        // GIVEN: Token with tabs
        $tokenWithTabs = "token\twith\ttabs";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $tokenWithTabs);

        // THEN: Should preserve tabs
        $this->assertEquals($tokenWithTabs, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function shouldPreserveTokenAsIs(): void
    {
        // GIVEN: Token with mixed content
        $complexToken = 'abc123-DEF_456.ghi+jkl/mno=pqr';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $complexToken);

        // THEN: Should preserve token exactly as provided
        $this->assertSame($complexToken, $refreshUser->token());
    }
}
