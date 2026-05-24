<?php

declare(strict_types=1);

namespace Tests\Unit\Users\DTOs;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\LoginDTO;

class LoginDTOTest extends TestCase
{
    /** @test */
    #[Test]
    public function should_create_instance_with_valid_credentials(): void
    {
        // GIVEN: Valid email and password
        $email = 'user@example.com';
        $password = 'password123';

        // WHEN: Creating LoginDTO
        $dto = new LoginDTO(email: $email, password: $password);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(LoginDTO::class, $dto);
        $this->assertEquals($email, $dto->email());
        $this->assertEquals($password, $dto->password());
    }

    /** @test */
    #[Test]
    public function should_create_from_request(): void
    {
        // GIVEN: Request data array
        $data = [
            'email' => 'user@example.com',
            'password' => 'password123',
        ];

        // WHEN: Creating from request
        $dto = LoginDTO::fromRequest($data);

        // THEN: Should create instance with correct data
        $this->assertInstanceOf(LoginDTO::class, $dto);
        $this->assertEquals('user@example.com', $dto->email());
        $this->assertEquals('password123', $dto->password());
    }

    /** @test */
    #[Test]
    public function should_return_correct_email(): void
    {
        // GIVEN: LoginDTO with specific email
        $email = 'test@example.com';
        $dto = new LoginDTO(email: $email, password: 'password123');

        // WHEN: Calling email()
        $result = $dto->email();

        // THEN: Should return correct email
        $this->assertEquals($email, $result);
    }

    /** @test */
    #[Test]
    public function should_return_correct_password(): void
    {
        // GIVEN: LoginDTO with specific password
        $password = 'securePassword123!@#';
        $dto = new LoginDTO(email: 'user@example.com', password: $password);

        // WHEN: Calling password()
        $result = $dto->password();

        // THEN: Should return correct password
        $this->assertEquals($password, $result);
    }

    /** @test */
    #[Test]
    public function should_handle_various_email_formats(): void
    {
        // GIVEN: Various valid email formats
        $validEmails = [
            'user@example.com',
            'user.name@example.com',
            'user+tag@example.co.uk',
            'user_name@subdomain.example.com',
            'user123@test-domain.com',
        ];

        foreach ($validEmails as $email) {
            // WHEN: Creating LoginDTO
            $dto = new LoginDTO(email: $email, password: 'password123');

            // THEN: Should handle email correctly
            $this->assertEquals($email, $dto->email());
        }
    }

    /** @test */
    #[Test]
    public function should_handle_various_password_lengths(): void
    {
        // GIVEN: Passwords of different lengths
        $passwords = [
            'pass',      // 4 chars
            'password',  // 8 chars
            'longpassword123',  // 15 chars
            str_repeat('a', 100),  // 100 chars
            str_repeat('a', 256),  // 256 chars
        ];

        foreach ($passwords as $password) {
            // WHEN: Creating LoginDTO
            $dto = new LoginDTO(email: 'user@example.com', password: $password);

            // THEN: Should handle password correctly
            $this->assertEquals($password, $dto->password());
        }
    }

    /** @test */
    #[Test]
    public function should_handle_special_characters_in_password(): void
    {
        // GIVEN: Password with special characters
        $specialPasswords = [
            'P@ssw0rd!',
            'P@$s%^&*()',
            'pass word with spaces',
            'пароль123',  // Cyrillic characters
            '密码123',    // Chinese characters
        ];

        foreach ($specialPasswords as $password) {
            // WHEN: Creating LoginDTO
            $dto = new LoginDTO(email: 'user@example.com', password: $password);

            // THEN: Should preserve special characters
            $this->assertEquals($password, $dto->password());
        }
    }

    /** @test */
    #[Test]
    public function should_be_readonly(): void
    {
        // GIVEN: LoginDTO instance
        $dto = new LoginDTO(email: 'user@example.com', password: 'password123');

        // THEN: Should be readonly
        $this->assertInstanceOf(LoginDTO::class, $dto);
        $reflection = new \ReflectionClass($dto);
        $this->assertTrue($reflection->isReadonly(), 'LoginDTO should be readonly');
    }

    /** @test */
    #[Test]
    public function should_handle_email_with_leading_trailing_spaces(): void
    {
        // GIVEN: Email with whitespace
        $emailWithSpaces = '  user@example.com  ';

        // WHEN: Creating LoginDTO (spaces are preserved by the class)
        $dto = new LoginDTO(email: $emailWithSpaces, password: 'password123');

        // THEN: Should preserve spaces as given
        $this->assertEquals($emailWithSpaces, $dto->email());
    }

    /** @test */
    #[Test]
    public function should_handle_empty_password(): void
    {
        // GIVEN: Empty password string
        $emptyPassword = '';

        // WHEN: Creating LoginDTO (no validation at DTO level)
        $dto = new LoginDTO(email: 'user@example.com', password: $emptyPassword);

        // THEN: Should create instance (validation happens in value object)
        $this->assertEquals($emptyPassword, $dto->password());
    }

    /** @test */
    #[Test]
    public function should_handle_from_request_with_extra_data(): void
    {
        // GIVEN: Request data with extra fields
        $data = [
            'email' => 'user@example.com',
            'password' => 'password123',
            'extra_field' => 'should_be_ignored',
            'another_field' => 'also_ignored',
        ];

        // WHEN: Creating from request
        $dto = LoginDTO::fromRequest($data);

        // THEN: Should use only email and password
        $this->assertEquals('user@example.com', $dto->email());
        $this->assertEquals('password123', $dto->password());
    }

    /** @test */
    #[Test]
    public function should_multiple_instances_be_independent(): void
    {
        // GIVEN: Two different LoginDTO instances
        $dto1 = new LoginDTO(email: 'user1@example.com', password: 'password1');
        $dto2 = new LoginDTO(email: 'user2@example.com', password: 'password2');

        // WHEN: Accessing their data
        $email1 = $dto1->email();
        $password1 = $dto1->password();
        $email2 = $dto2->email();
        $password2 = $dto2->password();

        // THEN: Each instance should have independent data
        $this->assertNotEquals($email1, $email2);
        $this->assertNotEquals($password1, $password2);
        $this->assertEquals('user1@example.com', $email1);
        $this->assertEquals('user2@example.com', $email2);
    }
}
