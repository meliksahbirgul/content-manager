<?php

declare(strict_types=1);

namespace Tests\Unit\Users\ValueObjects;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Users\Application\DTOs\LoginDTO;
use Source\Users\Domain\ValueObjects\LoginUser;

class LoginUserTest extends TestCase
{
    /** @test */
    #[Test]
    public function shouldCreateInstanceWithValidCredentials(): void
    {
        // GIVEN: Valid email and password
        $email = 'user@example.com';
        $password = 'password123';

        // WHEN: Creating LoginUser
        $loginUser = new LoginUser(email: $email, password: $password);

        // THEN: Should create instance successfully
        $this->assertInstanceOf(LoginUser::class, $loginUser);
        $this->assertEquals($email, $loginUser->email());
        $this->assertEquals($password, $loginUser->password());
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenEmailIsEmpty(): void
    {
        // GIVEN: Empty email
        $emptyEmail = '';
        $password = 'password123';

        // THEN: Should throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email or password can not be empty.');

        // WHEN: Creating LoginUser with empty email
        new LoginUser(email: $emptyEmail, password: $password);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenPasswordIsEmpty(): void
    {
        // GIVEN: Empty password
        $email = 'user@example.com';
        $emptyPassword = '';

        // THEN: Should throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email or password can not be empty.');

        // WHEN: Creating LoginUser with empty password
        new LoginUser(email: $email, password: $emptyPassword);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenEmailIsInvalid(): void
    {
        // GIVEN: Invalid email formats
        $invalidEmails = [
            'notanemail',
            'missing@domain',
            '@example.com',
            'user@',
            'user @example.com',
            'user@.com',
        ];

        foreach ($invalidEmails as $invalidEmail) {
            // THEN: Should throw InvalidArgumentException
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid email address.');

            // WHEN: Creating LoginUser with invalid email
            new LoginUser(email: $invalidEmail, password: 'password123');
        }
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenPasswordIsTooShort(): void
    {
        // GIVEN: Password shorter than 8 characters
        $email = 'user@example.com';
        $shortPasswords = [
            'pass',
            'pass12',
            'p',
            '',
        ];

        foreach ($shortPasswords as $shortPassword) {
            // THEN: Should throw InvalidArgumentException
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Invalid password');

            // WHEN: Creating LoginUser with short password
            new LoginUser(email: $email, password: $shortPassword);
        }
    }

    /** @test */
    #[Test]
    public function shouldAcceptValidEmails(): void
    {
        // GIVEN: Valid email formats
        $validEmails = [
            'user@example.com',
            'user.name@example.com',
            'user+tag@example.co.uk',
            'user_name@subdomain.example.com',
            'user123@test-domain.com',
            'a@b.co',
        ];

        foreach ($validEmails as $validEmail) {
            // WHEN: Creating LoginUser with valid email
            $loginUser = new LoginUser(email: $validEmail, password: 'password123');

            // THEN: Should create successfully
            $this->assertEquals($validEmail, $loginUser->email());
        }
    }

    /** @test */
    #[Test]
    public function shouldAcceptPasswordWithMinimumEightCharacters(): void
    {
        // GIVEN: Password with exactly 8 characters
        $email = 'user@example.com';
        $minPassword = 'pass1234';

        // WHEN: Creating LoginUser with minimum password length
        $loginUser = new LoginUser(email: $email, password: $minPassword);

        // THEN: Should create successfully
        $this->assertEquals($minPassword, $loginUser->password());
    }

    /** @test */
    #[Test]
    public function shouldAcceptLongPasswords(): void
    {
        // GIVEN: Long password
        $email = 'user@example.com';
        $longPassword = str_repeat('a', 5000);

        // WHEN: Creating LoginUser with long password
        $loginUser = new LoginUser(email: $email, password: $longPassword);

        // THEN: Should create successfully
        $this->assertEquals($longPassword, $loginUser->password());
    }

    /** @test */
    #[Test]
    public function shouldAcceptPasswordsWithSpecialCharacters(): void
    {
        // GIVEN: Passwords with special characters
        $specialPasswords = [
            'P@ssw0rd!',
            'P@$s%^&*()',
            'pass word with spaces',
            'pass/with/slashes',
            'pass\\with\\backslashes',
        ];

        $email = 'user@example.com';

        foreach ($specialPasswords as $specialPassword) {
            // WHEN: Creating LoginUser
            $loginUser = new LoginUser(email: $email, password: $specialPassword);

            // THEN: Should preserve special characters
            $this->assertEquals($specialPassword, $loginUser->password());
        }
    }

    /** @test */
    #[Test]
    public function shouldCreateFromDTO(): void
    {
        // GIVEN: LoginDTO with valid credentials
        $dto = new LoginDTO(
            email: 'user@example.com',
            password: 'password123',
        );

        // WHEN: Creating LoginUser from DTO
        $loginUser = LoginUser::createFromDTO($dto);

        // THEN: Should create successfully
        $this->assertInstanceOf(LoginUser::class, $loginUser);
        $this->assertEquals('user@example.com', $loginUser->email());
        $this->assertEquals('password123', $loginUser->password());
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionWhenCreatingFromDTOWithInvalidData(): void
    {
        // GIVEN: LoginDTO with invalid credentials
        $dto = new LoginDTO(
            email: 'invalid-email',
            password: 'short',
        );

        // THEN: Should throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);

        // WHEN: Creating LoginUser from DTO
        LoginUser::createFromDTO($dto);
    }

    /** @test */
    #[Test]
    public function shouldValidateUponInstantiation(): void
    {
        // GIVEN: Invalid credentials
        $testCases = [
            ['email' => '', 'password' => 'password123', 'expectedMessage' => 'Email or password can not be empty.'],
            ['email' => 'user@example.com', 'password' => '', 'expectedMessage' => 'Email or password can not be empty.'],
            ['email' => 'invalid', 'password' => 'password123', 'expectedMessage' => 'Invalid email address.'],
            ['email' => 'user@example.com', 'password' => 'short', 'expectedMessage' => 'Invalid password'],
        ];

        foreach ($testCases as $testCase) {
            // THEN: Should throw exception with correct message
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage($testCase['expectedMessage']);

            // WHEN: Creating LoginUser
            new LoginUser(email: $testCase['email'], password: $testCase['password']);
        }
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectEmail(): void
    {
        // GIVEN: LoginUser with specific email
        $email = 'testuser@example.com';
        $loginUser = new LoginUser(email: $email, password: 'password123');

        // WHEN: Calling email()
        $result = $loginUser->email();

        // THEN: Should return correct email
        $this->assertEquals($email, $result);
    }

    /** @test */
    #[Test]
    public function shouldReturnCorrectPassword(): void
    {
        // GIVEN: LoginUser with specific password
        $password = 'securePassword123!@#';
        $loginUser = new LoginUser(email: 'user@example.com', password: $password);

        // WHEN: Calling password()
        $result = $loginUser->password();

        // THEN: Should return correct password
        $this->assertEquals($password, $result);
    }

    /** @test */
    #[Test]
    public function shouldHandleMultipleInstances(): void
    {
        // GIVEN: Multiple LoginUser instances
        $user1 = new LoginUser(email: 'user1@example.com', password: 'password1234');
        $user2 = new LoginUser(email: 'user2@example.com', password: 'password5678');

        // WHEN: Accessing their data
        $email1 = $user1->email();
        $password1 = $user1->password();
        $email2 = $user2->email();
        $password2 = $user2->password();

        // THEN: Instances should be independent
        $this->assertNotEquals($email1, $email2);
        $this->assertNotEquals($password1, $password2);
        $this->assertEquals('user1@example.com', $email1);
        $this->assertEquals('user2@example.com', $email2);
    }

    /** @test */
    #[Test]
    public function shouldHandleCaseSensitiveEmails(): void
    {
        // GIVEN: Emails with different cases
        $emailLower = 'user@example.com';
        $emailUpper = 'USER@EXAMPLE.COM';
        $emailMixed = 'User@Example.Com';

        // WHEN: Creating LoginUser instances
        $user1 = new LoginUser(email: $emailLower, password: 'password123');
        $user2 = new LoginUser(email: $emailUpper, password: 'password123');
        $user3 = new LoginUser(email: $emailMixed, password: 'password123');

        // THEN: Should preserve case as given
        $this->assertEquals($emailLower, $user1->email());
        $this->assertEquals($emailUpper, $user2->email());
        $this->assertEquals($emailMixed, $user3->email());
    }

    /** @test */
    #[Test]
    public function shouldAcceptComplexEmailAddresses(): void
    {
        // GIVEN: Complex but valid email addresses
        $complexEmails = [
            'first.last+tag@example.co.uk',
            'test_email@sub.domain.example.com',
            'user123@example-domain.org',
        ];

        foreach ($complexEmails as $email) {
            // WHEN: Creating LoginUser
            $loginUser = new LoginUser(email: $email, password: 'password123');

            // THEN: Should accept complex emails
            $this->assertEquals($email, $loginUser->email());
        }
    }
}
