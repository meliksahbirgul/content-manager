<?php

declare(strict_types=1);

namespace Tests\Unit\Users\Entities;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Source\Users\Domain\Entity\UserEntity;

class UserEntityTest extends TestCase
{
    /** @test */
    #[Test]
    public function should_create_instance_with_valid_data(): void
    {
        // GIVEN: Valid user data
        $uuid = Uuid::uuid7()->toString();
        $name = 'John Doe';
        $email = 'john@example.com';
        $password = 'hashedpassword123';

        // WHEN: Creating UserEntity
        $user = new UserEntity(
            uuid: $uuid,
            name: $name,
            email: $email,
            password: $password,
        );

        // THEN: Should create instance with correct data
        $this->assertInstanceOf(UserEntity::class, $user);
        $this->assertEquals($uuid, $user->uuid());
        $this->assertEquals($name, $user->name());
        $this->assertEquals($email, $user->email());
        $this->assertEquals($password, $user->password());
    }

    /** @test */
    #[Test]
    public function should_return_uuid_correctly(): void
    {
        // GIVEN: UserEntity with specific uuid
        $uuid = Uuid::uuid7()->toString();
        $user = new UserEntity(
            uuid: $uuid,
            name: 'John Doe',
            email: 'john@example.com',
            password: 'hashedpassword123',
        );

        // WHEN: Calling uuid()
        $result = $user->uuid();

        // THEN: Should return the exact uuid provided
        $this->assertEquals($uuid, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function should_return_name_correctly(): void
    {
        // GIVEN: UserEntity with specific name
        $name = 'Jane Smith';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: 'jane@example.com',
            password: 'password123',
        );

        // WHEN: Calling name()
        $result = $user->name();

        // THEN: Should return the exact name provided
        $this->assertEquals($name, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function should_return_email_correctly(): void
    {
        // GIVEN: UserEntity with specific email
        $email = 'user@example.com';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: $email,
            password: 'password123',
        );

        // WHEN: Calling email()
        $result = $user->email();

        // THEN: Should return the exact email provided
        $this->assertEquals($email, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function should_return_password_correctly(): void
    {
        // GIVEN: UserEntity with specific password
        $password = '$2y$10$hashedpasswordstring';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: 'user@example.com',
            password: $password,
        );

        // WHEN: Calling password()
        $result = $user->password();

        // THEN: Should return the exact password provided
        $this->assertEquals($password, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function should_handle_empty_string_name(): void
    {
        // GIVEN: UserEntity with empty name
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: '',
            email: 'user@example.com',
            password: 'password123',
        );

        // WHEN: Calling name()
        $result = $user->name();

        // THEN: Should return empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function should_handle_empty_string_email(): void
    {
        // GIVEN: UserEntity with empty email
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: '',
            password: 'password123',
        );

        // WHEN: Calling email()
        $result = $user->email();

        // THEN: Should return empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function should_handle_empty_string_password(): void
    {
        // GIVEN: UserEntity with empty password
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: 'user@example.com',
            password: '',
        );

        // WHEN: Calling password()
        $result = $user->password();

        // THEN: Should return empty string
        $this->assertEquals('', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    #[Test]
    public function should_handle_very_long_name(): void
    {
        // GIVEN: UserEntity with very long name (1000+ chars)
        $longName = str_repeat('A', 1000);
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $longName,
            email: 'user@example.com',
            password: 'password123',
        );

        // WHEN: Calling name()
        $result = $user->name();

        // THEN: Should preserve entire name
        $this->assertEquals($longName, $result);
        $this->assertStringStartsWith('AAA', $result);
        $this->assertStringEndsWith('AAA', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_very_long_email(): void
    {
        // GIVEN: UserEntity with very long email
        $longEmail = str_repeat('a', 200).'@example.com';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: $longEmail,
            password: 'password123',
        );

        // WHEN: Calling email()
        $result = $user->email();

        // THEN: Should preserve entire email
        $this->assertEquals($longEmail, $result);
        $this->assertStringEndsWith('@example.com', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_very_long_password(): void
    {
        // GIVEN: UserEntity with very long password/hash
        $longPassword = str_repeat('a', 1000);
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: 'user@example.com',
            password: $longPassword,
        );

        // WHEN: Calling password()
        $result = $user->password();

        // THEN: Should preserve entire password
        $this->assertEquals($longPassword, $result);
    }

    /** @test */
    #[Test]
    public function should_handle_special_characters_in_name(): void
    {
        // GIVEN: Name with special characters
        $name = "O'Brien-Smith, Jr. (PhD)";
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: 'user@example.com',
            password: 'password123',
        );

        // WHEN: Calling name()
        $result = $user->name();

        // THEN: Should preserve all special characters
        $this->assertEquals($name, $result);
        $this->assertStringContainsString("'", $result);
        $this->assertStringContainsString('-', $result);
        $this->assertStringContainsString(',', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_special_characters_in_email(): void
    {
        // GIVEN: Email with special characters
        $email = 'user+tag.name@sub-domain.co.uk';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: $email,
            password: 'password123',
        );

        // WHEN: Calling email()
        $result = $user->email();

        // THEN: Should preserve all email special characters
        $this->assertEquals($email, $result);
        $this->assertStringContainsString('+', $result);
        $this->assertStringContainsString('.', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_unicode_in_name(): void
    {
        // GIVEN: Name with unicode characters
        $name = '佐藤 太郎 (日本)';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: 'user@example.com',
            password: 'password123',
        );

        // WHEN: Calling name()
        $result = $user->name();

        // THEN: Should preserve unicode characters
        $this->assertEquals($name, $result);
        $this->assertStringContainsString('佐藤', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_unicode_in_email(): void
    {
        // GIVEN: Email with unicode domain (IDN)
        $email = 'user@münchen.de';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: $email,
            password: 'password123',
        );

        // WHEN: Calling email()
        $result = $user->email();

        // THEN: Should preserve unicode
        $this->assertEquals($email, $result);
        $this->assertStringContainsString('ü', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_b_crypt_hash_password(): void
    {
        // GIVEN: BCrypt hashed password
        $bcryptHash = '$2y$10$kUD2D1VUx7mqVVYDsuE4be6fI9kFIzMHzMquI.zPNlpPnOEgwJ0lu';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: 'user@example.com',
            password: $bcryptHash,
        );

        // WHEN: Calling password()
        $result = $user->password();

        // THEN: Should preserve BCrypt hash exactly
        $this->assertEquals($bcryptHash, $result);
        $this->assertStringStartsWith('$2y$', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_argon2_password_hash(): void
    {
        // GIVEN: Argon2 hashed password
        $argon2Hash = '$argon2id$v=19$m=65536,t=4,p=1$hash$1234567890abcdef';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: 'user@example.com',
            password: $argon2Hash,
        );

        // WHEN: Calling password()
        $result = $user->password();

        // THEN: Should preserve Argon2 hash exactly
        $this->assertEquals($argon2Hash, $result);
        $this->assertStringStartsWith('$argon2', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_whitespace_in_name(): void
    {
        // GIVEN: Name with various whitespace
        $name = "  John   Doe  \t\n";
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: 'user@example.com',
            password: 'password123',
        );

        // WHEN: Calling name()
        $result = $user->name();

        // THEN: Should preserve all whitespace as-is
        $this->assertEquals($name, $result);
        $this->assertStringStartsWith('  ', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_whitespace_in_email(): void
    {
        // GIVEN: Email with spaces (invalid but testing preservation)
        $email = '  user@example.com  ';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: $email,
            password: 'password123',
        );

        // WHEN: Calling email()
        $result = $user->email();

        // THEN: Should preserve whitespace
        $this->assertEquals($email, $result);
        $this->assertStringStartsWith('  ', $result);
    }

    /** @test */
    #[Test]
    public function should_handle_null_byte_in_name(): void
    {
        // GIVEN: Name with null byte (injection attempt)
        $name = "John\0Doe";
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: 'user@example.com',
            password: 'password123',
        );

        // WHEN: Calling name()
        $result = $user->name();

        // THEN: Should preserve null byte
        $this->assertEquals($name, $result);
        $this->assertStringContainsString("\0", $result);
    }

    /** @test */
    #[Test]
    public function should_handle_null_byte_in_password(): void
    {
        // GIVEN: Password with null byte
        $password = "password\0123";
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: 'user@example.com',
            password: $password,
        );

        // WHEN: Calling password()
        $result = $user->password();

        // THEN: Should preserve null byte
        $this->assertEquals($password, $result);
    }

    /** @test */
    #[Test]
    public function should_maintain_data_immutability_across_multiple_calls(): void
    {
        // GIVEN: UserEntity instance
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'John Doe',
            email: 'john@example.com',
            password: 'hashedpassword',
        );

        // WHEN: Calling methods multiple times
        $name1 = $user->name();
        $email1 = $user->email();
        $password1 = $user->password();

        $name2 = $user->name();
        $email2 = $user->email();
        $password2 = $user->password();

        // THEN: Values should remain consistent
        $this->assertEquals($name1, $name2);
        $this->assertEquals($email1, $email2);
        $this->assertEquals($password1, $password2);
    }

    /** @test */
    #[Test]
    public function should_not_affect_other_instances_when_creating_new(): void
    {
        // GIVEN: Multiple UserEntity instances
        $user1 = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User One',
            email: 'user1@example.com',
            password: 'password1',
        );

        $user2 = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Two',
            email: 'user2@example.com',
            password: 'password2',
        );

        // WHEN: Getting values from both
        $name1 = $user1->name();
        $name2 = $user2->name();

        // THEN: Each instance maintains its own data
        $this->assertEquals('User One', $name1);
        $this->assertEquals('User Two', $name2);
        $this->assertNotEquals($name1, $name2);
    }

    /** @test */
    #[Test]
    public function should_handle_numeric_string_name(): void
    {
        // GIVEN: Numeric string as name
        $name = '123456789';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: 'user@example.com',
            password: 'password123',
        );

        // WHEN: Calling name()
        $result = $user->name();

        // THEN: Should preserve numeric string
        $this->assertEquals($name, $result);
        $this->assertIsString($result);
    }

    /** @test */
    #[Test]
    public function should_handle_numeric_string_email(): void
    {
        // GIVEN: Numeric-like string as email
        $email = '123@456.789';
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'User Name',
            email: $email,
            password: 'password123',
        );

        // WHEN: Calling email()
        $result = $user->email();

        // THEN: Should preserve numeric email
        $this->assertEquals($email, $result);
    }

    /** @test */
    #[Test]
    public function should_preserve_exact_casing(): void
    {
        // GIVEN: Mixed case data
        $name = 'JoHn DoE';
        $email = 'User@Example.COM';
        $password = 'PassWord123';

        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: $email,
            password: $password,
        );

        // WHEN: Getting values
        $resultName = $user->name();
        $resultEmail = $user->email();
        $resultPassword = $user->password();

        // THEN: Should preserve exact casing
        $this->assertEquals('JoHn DoE', $resultName);
        $this->assertEquals('User@Example.COM', $resultEmail);
        $this->assertEquals('PassWord123', $resultPassword);
    }

    /** @test */
    #[Test]
    public function should_handle_single_character_values(): void
    {
        // GIVEN: Single character values
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'A',
            email: 'B',
            password: 'C',
        );

        // WHEN: Calling methods
        $name = $user->name();
        $email = $user->email();
        $password = $user->password();

        // THEN: Should return single characters
        $this->assertEquals('A', $name);
        $this->assertEquals('B', $email);
        $this->assertEquals('C', $password);
    }

    /** @test */
    #[Test]
    public function should_return_string_type(): void
    {
        // GIVEN: UserEntity instance
        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: 'John',
            email: 'john@example.com',
            password: 'hash',
        );

        // WHEN: Getting all values
        $name = $user->name();
        $email = $user->email();
        $password = $user->password();

        // THEN: All should be strings
        $this->assertIsString($name);
        $this->assertIsString($email);
        $this->assertIsString($password);
    }

    /** @test */
    #[Test]
    public function should_handle_newline_characters(): void
    {
        // GIVEN: Data with newline characters
        $name = "John\nDoe";
        $email = "user\n@example.com";
        $password = "pass\nword";

        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: $email,
            password: $password,
        );

        // WHEN: Getting values
        $resultName = $user->name();
        $resultEmail = $user->email();
        $resultPassword = $user->password();

        // THEN: Should preserve newlines
        $this->assertStringContainsString("\n", $resultName);
        $this->assertStringContainsString("\n", $resultEmail);
        $this->assertStringContainsString("\n", $resultPassword);
    }

    /** @test */
    #[Test]
    public function should_handle_tab_characters(): void
    {
        // GIVEN: Data with tab characters
        $name = "John\tDoe";
        $email = "user\t@example.com";
        $password = "pass\tword";

        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: $email,
            password: $password,
        );

        // WHEN: Getting values
        $resultName = $user->name();
        $resultEmail = $user->email();
        $resultPassword = $user->password();

        // THEN: Should preserve tabs
        $this->assertStringContainsString("\t", $resultName);
        $this->assertStringContainsString("\t", $resultEmail);
        $this->assertStringContainsString("\t", $resultPassword);
    }

    /** @test */
    #[Test]
    public function should_handle_carriage_return_characters(): void
    {
        // GIVEN: Data with carriage return characters
        $name = "John\rDoe";
        $email = "user\r@example.com";
        $password = "pass\rword";

        $user = new UserEntity(
            uuid: Uuid::uuid7()->toString(),
            name: $name,
            email: $email,
            password: $password,
        );

        // WHEN: Getting values
        $resultName = $user->name();
        $resultEmail = $user->email();
        $resultPassword = $user->password();

        // THEN: Should preserve carriage returns
        $this->assertStringContainsString("\r", $resultName);
        $this->assertStringContainsString("\r", $resultEmail);
        $this->assertStringContainsString("\r", $resultPassword);
    }
}
