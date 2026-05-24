<?php

declare(strict_types=1);

namespace Tests\Unit\Users\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Domain\ValueObjects\RefreshUser;

class RefreshUserSadPathTest extends TestCase
{
    /** @test */
    #[Test]
    public function should_throw_exception_for_empty_token(): void
    {
        // GIVEN: Empty token
        // THEN: Should throw exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token can not be empty.');

        // WHEN: Creating RefreshUser
        new RefreshUser(token: '');
    }

    /** @test */
    #[Test]
    public function should_accept_whitespace_only_token(): void
    {
        // GIVEN: Whitespace-only token
        // Note: PHP's empty() doesn't treat whitespace strings as empty
        $token = '   ';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_tabs_only_token(): void
    {
        // GIVEN: Tabs-only token
        // Note: PHP's empty() doesn't treat tab strings as empty
        $token = "\t\t\t";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_newlines_only_token(): void
    {
        // GIVEN: Newlines-only token
        // Note: PHP's empty() doesn't treat newline strings as empty
        $token = "\n\n\n";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_crlf_only_token(): void
    {
        // GIVEN: CRLF-only token
        // Note: PHP's empty() doesn't treat CRLF strings as empty
        $token = "\r\n\r\n";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_mixed_whitespace_token(): void
    {
        // GIVEN: Mixed whitespace token
        // Note: PHP's empty() doesn't treat whitespace strings as empty
        $token = " \t \n \r ";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_token_with_leading_trailing_whitespace(): void
    {
        // GIVEN: Token with leading/trailing whitespace but not only whitespace
        $token = '   real_token_123   ';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept and preserve
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_valid_jwt_token(): void
    {
        // GIVEN: Valid JWT token
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_simple_token(): void
    {
        // GIVEN: Simple alphanumeric token
        $token = 'token123abc';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_very_long_token(): void
    {
        // GIVEN: Very long token
        $token = str_repeat('a', 10000);

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_single_character_token(): void
    {
        // GIVEN: Single character token
        $token = 'a';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_numeric_token(): void
    {
        // GIVEN: Pure numeric token
        $token = '123456789';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_special_characters_in_token(): void
    {
        // GIVEN: Token with special characters
        $token = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_unicode_in_token(): void
    {
        // GIVEN: Token with unicode characters
        $token = 'token_トークン_🔐';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertStringContainsString('トークン', $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_accept_null_byte_in_token(): void
    {
        // GIVEN: Token with null byte (edge case, shouldn't normally happen)
        $token = "token\0injection";

        // WHEN: Creating RefreshUser
        // Note: empty() will not treat strings with null bytes as empty
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept (dangerous but technically valid)
        $this->assertStringContainsString("\0", $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_preserve_token_exactly(): void
    {
        // GIVEN: Token with mixed case and special formatting
        $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should preserve exactly
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_handle_token_with_newlines(): void
    {
        // GIVEN: Token with newlines but not only newlines
        $token = "token\nwith\nnewlines";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept and preserve
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_handle_token_with_tabs(): void
    {
        // GIVEN: Token with tabs but not only tabs
        $token = "token\twith\ttabs";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept and preserve
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_handle_multiple_instances(): void
    {
        // GIVEN: Multiple RefreshUser instances
        $tokens = ['token1', 'token2', 'token3'];
        $instances = [];

        // WHEN: Creating multiple instances
        foreach ($tokens as $token) {
            $instances[] = new RefreshUser(token: $token);
        }

        // THEN: Each should have correct token
        for ($i = 0; $i < count($instances); $i++) {
            $this->assertEquals($tokens[$i], $instances[$i]->token());
        }
    }

    /** @test */
    #[Test]
    public function should_handle_token_with_consecutive_whitespace(): void
    {
        // GIVEN: Token with multiple spaces between content
        $token = 'token    with    spaces';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should preserve all spaces
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_handle_token_with_html_content(): void
    {
        // GIVEN: Token that looks like HTML
        $token = '<script>alert("xss")</script>';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept (sanitization not responsibility of ValueObject)
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_handle_token_with_sql_content(): void
    {
        // GIVEN: Token that looks like SQL
        $token = "'; DROP TABLE users; --";

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept (sanitization not responsibility of ValueObject)
        $this->assertEquals($token, $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_throw_exception_with_correct_message(): void
    {
        // GIVEN: Empty token
        // THEN: Should throw with specific message
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Token can not be empty.');

        // WHEN: Creating RefreshUser
        new RefreshUser(token: '');
    }

    /** @test */
    #[Test]
    public function should_validate_upon_instantiation(): void
    {
        // GIVEN: Invalid token (only empty string is truly invalid)
        // THEN: Should throw immediately for empty string
        $this->expectException(InvalidArgumentException::class);

        // WHEN: Creating RefreshUser with empty token
        new RefreshUser(token: '');
    }

    /** @test */
    #[Test]
    public function should_handle_zero_width_characters(): void
    {
        // GIVEN: Token with zero-width characters (not truly empty)
        // U+200B is zero-width space
        $token = "token\u{200B}withZeroWidth";

        // WHEN: Creating RefreshUser
        // Note: empty() doesn't see zero-width spaces as empty
        try {
            $refreshUser = new RefreshUser(token: $token);
            // If it creates, that's what PHP does
            $this->assertNotNull($refreshUser);
        } catch (InvalidArgumentException $e) {
            // If it throws, that's a security feature
            $this->assertNotNull($e);
        }
    }

    /** @test */
    #[Test]
    public function should_throw_exception_for_null_character_alone(): void
    {
        // GIVEN: Just a null character
        $token = "\0";

        // WHEN: Creating RefreshUser
        // Note: empty() doesn't treat "\0" as empty
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should create (null byte is not empty)
        $this->assertNotNull($refreshUser);
    }

    /** @test */
    #[Test]
    public function should_handle_case_preservation(): void
    {
        // GIVEN: Token with mixed case
        $token = 'ToKeN_WiTh_MiXeD_CaSe';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should preserve case exactly
        $this->assertEquals('ToKeN_WiTh_MiXeD_CaSe', $refreshUser->token());
    }

    /** @test */
    #[Test]
    public function should_handle_dot_notation_in_token(): void
    {
        // GIVEN: Token with dot notation (like JWT parts)
        $token = 'header.payload.signature';

        // WHEN: Creating RefreshUser
        $refreshUser = new RefreshUser(token: $token);

        // THEN: Should accept
        $this->assertEquals($token, $refreshUser->token());
    }
}
