<?php

declare(strict_types=1);

namespace Tests\Unit\Pages\Enums;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Source\Pages\Domain\Enums\PageStatus;

class PageStatusTest extends TestCase
{
    /** @test */
    #[Test]
    public function should_have_active_case(): void
    {
        // WHEN: Accessing ACTIVE case
        $status = PageStatus::ACTIVE;

        // THEN: Should be a valid PageStatus enum
        $this->assertInstanceOf(PageStatus::class, $status);
        $this->assertEquals('active', $status->value);
    }

    /** @test */
    #[Test]
    public function should_have_passive_case(): void
    {
        // WHEN: Accessing PASSIVE case
        $status = PageStatus::PASSIVE;

        // THEN: Should be a valid PageStatus enum
        $this->assertInstanceOf(PageStatus::class, $status);
        $this->assertEquals('passive', $status->value);
    }

    /** @test */
    #[Test]
    public function should_have_correct_values(): void
    {
        // THEN: Enum values should be correct strings
        $this->assertEquals('active', PageStatus::ACTIVE->value);
        $this->assertEquals('passive', PageStatus::PASSIVE->value);
    }

    /** @test */
    #[Test]
    public function should_create_from_string_active(): void
    {
        // GIVEN: String value 'active'
        $value = 'active';

        // WHEN: Creating enum from string
        $status = PageStatus::fromString($value);

        // THEN: Should return ACTIVE case
        $this->assertEquals(PageStatus::ACTIVE, $status);
        $this->assertInstanceOf(PageStatus::class, $status);
    }

    /** @test */
    #[Test]
    public function should_create_from_string_passive(): void
    {
        // GIVEN: String value 'passive'
        $value = 'passive';

        // WHEN: Creating enum from string
        $status = PageStatus::fromString($value);

        // THEN: Should return PASSIVE case
        $this->assertEquals(PageStatus::PASSIVE, $status);
        $this->assertInstanceOf(PageStatus::class, $status);
    }

    /** @test */
    #[Test]
    public function should_throw_exception_for_invalid_string(): void
    {
        // GIVEN: Invalid string value
        $invalidValue = 'invalid';

        // THEN: Should throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid page status: $invalidValue");

        // WHEN: Calling fromString with invalid value
        PageStatus::fromString($invalidValue);
    }

    /** @test */
    #[Test]
    public function should_throw_exception_for_unknown_status(): void
    {
        // GIVEN: Unknown status string
        $unknownStatus = 'unknown';

        // THEN: Should throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid page status: $unknownStatus");

        // WHEN: Calling fromString
        PageStatus::fromString($unknownStatus);
    }

    /** @test */
    #[Test]
    public function should_throw_exception_for_empty_string(): void
    {
        // GIVEN: Empty string
        $emptyString = '';

        // THEN: Should throw InvalidArgumentException
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid page status: $emptyString");

        // WHEN: Calling fromString
        PageStatus::fromString($emptyString);
    }

    /** @test */
    #[Test]
    public function should_throw_exception_for_case_sensitive(): void
    {
        // GIVEN: Incorrect case strings
        $invalidValues = ['Active', 'ACTIVE', 'Passive', 'PASSIVE'];

        foreach ($invalidValues as $invalidValue) {
            // THEN: Should throw InvalidArgumentException for each
            $this->expectException(InvalidArgumentException::class);

            // WHEN: Calling fromString
            PageStatus::fromString($invalidValue);
        }
    }

    /** @test */
    #[Test]
    public function should_compare_cases_correctly(): void
    {
        // GIVEN: Two references to ACTIVE case
        $status1 = PageStatus::ACTIVE;
        $status2 = PageStatus::ACTIVE;

        // THEN: Should be identical
        $this->assertSame($status1, $status2);
        $this->assertEquals($status1, $status2);
    }

    /** @test */
    #[Test]
    public function should_compare_different_cases_correctly(): void
    {
        // GIVEN: ACTIVE and PASSIVE cases
        $active = PageStatus::ACTIVE;
        $passive = PageStatus::PASSIVE;

        // THEN: Should not be identical or equal
        $this->assertNotSame($active, $passive);
        $this->assertNotEquals($active, $passive);
    }

    /** @test */
    #[Test]
    public function should_return_string_value(): void
    {
        // GIVEN: PageStatus enum
        $status = PageStatus::ACTIVE;

        // WHEN: Accessing value property
        $value = $status->value;

        // THEN: Should return string
        $this->assertIsString($value);
        $this->assertEquals('active', $value);
    }

    /** @test */
    #[Test]
    public function should_have_name_property(): void
    {
        // GIVEN: PageStatus enum
        $activeStatus = PageStatus::ACTIVE;
        $passiveStatus = PageStatus::PASSIVE;

        // THEN: Should have correct name properties
        $this->assertEquals('ACTIVE', $activeStatus->name);
        $this->assertEquals('PASSIVE', $passiveStatus->name);
    }

    /** @test */
    #[Test]
    public function should_support_from_method(): void
    {
        // WHEN: Using from() method with value
        $status = PageStatus::from('active');

        // THEN: Should return ACTIVE case
        $this->assertEquals(PageStatus::ACTIVE, $status);
    }

    /** @test */
    #[Test]
    public function should_throw_exception_from_method_with_invalid_value(): void
    {
        // THEN: Should throw ValueError (built-in enum behavior)
        $this->expectException(\ValueError::class);

        // WHEN: Using from() with invalid value
        PageStatus::from('invalid');
    }

    /** @test */
    #[Test]
    public function should_support_try_from_method(): void
    {
        // WHEN: Using tryFrom() method with valid value
        $status = PageStatus::tryFrom('active');

        // THEN: Should return ACTIVE case
        $this->assertEquals(PageStatus::ACTIVE, $status);
    }

    /** @test */
    #[Test]
    public function should_return_null_from_try_from_with_invalid_value(): void
    {
        // WHEN: Using tryFrom() with invalid value
        $status = PageStatus::tryFrom('invalid');

        // THEN: Should return null
        $this->assertNull($status);
    }

    /** @test */
    #[Test]
    public function should_be_castable_to_string(): void
    {
        // GIVEN: PageStatus enum
        $status = PageStatus::ACTIVE;

        // WHEN: Converting to string using value property
        $stringValue = $status->value;

        // THEN: Should return string representation
        $this->assertEquals('active', $stringValue);
        $this->assertIsString($stringValue);
    }

    /** @test */
    #[Test]
    public function should_handle_multiple_from_string_calls(): void
    {
        // GIVEN: Multiple calls to fromString
        $status1 = PageStatus::fromString('active');
        $status2 = PageStatus::fromString('passive');
        $status3 = PageStatus::fromString('active');

        // THEN: Same values should return same references
        $this->assertSame($status1, $status3);
        $this->assertNotSame($status1, $status2);
        $this->assertEquals(PageStatus::ACTIVE, $status1);
        $this->assertEquals(PageStatus::PASSIVE, $status2);
    }

    /** @test */
    #[Test]
    public function should_be_usable_in_switch_statement(): void
    {
        // GIVEN: Various PageStatus values
        $testStatuses = [PageStatus::ACTIVE, PageStatus::PASSIVE];

        foreach ($testStatuses as $status) {
            // WHEN: Using in switch statement
            $result = match ($status) {
                PageStatus::ACTIVE => 'page is active',
                PageStatus::PASSIVE => 'page is passive',
            };

            // THEN: Should match correctly
            if ($status === PageStatus::ACTIVE) {
                $this->assertEquals('page is active', $result);
            } else {
                $this->assertEquals('page is passive', $result);
            }
        }
    }

    /** @test */
    #[Test]
    public function should_be_comparable_with_value(): void
    {
        // GIVEN: PageStatus enum
        $status = PageStatus::ACTIVE;

        // THEN: Value can be compared directly
        $this->assertTrue($status->value === 'active');
        $this->assertFalse($status->value === 'passive');
    }

    /** @test */
    #[Test]
    public function should_support_in_array(): void
    {
        // GIVEN: Array of allowed statuses
        $allowedStatuses = [PageStatus::ACTIVE, PageStatus::PASSIVE];

        // THEN: Can use in_array to check
        $this->assertContains(PageStatus::ACTIVE, $allowedStatuses);
        $this->assertContains(PageStatus::PASSIVE, $allowedStatuses);

        // Create invalid status through from() and test
        $testStatus = PageStatus::ACTIVE;
        $this->assertContains($testStatus, $allowedStatuses);
    }

    /** @test */
    #[Test]
    public function should_support_array_access(): void
    {
        // GIVEN: Collection of statuses
        $statuses = [
            'active' => PageStatus::ACTIVE,
            'passive' => PageStatus::PASSIVE,
        ];

        // THEN: Should support array access
        $this->assertEquals(PageStatus::ACTIVE, $statuses['active']);
        $this->assertEquals(PageStatus::PASSIVE, $statuses['passive']);
    }

    /** @test */
    #[Test]
    public function should_be_serializable(): void
    {
        // GIVEN: PageStatus enum
        $status = PageStatus::ACTIVE;

        // WHEN: Converting to JSON via value
        $json = json_encode(['status' => $status->value]);

        // THEN: Should serialize correctly
        $this->assertJsonStringEqualsJsonString('{"status":"active"}', $json);
    }

    /** @test */
    #[Test]
    public function should_handle_from_string_with_whitespace(): void
    {
        // GIVEN: Strings with whitespace
        $invalidValues = [' active', 'active ', ' active '];

        foreach ($invalidValues as $invalidValue) {
            // THEN: Should throw exception for whitespace variants
            $this->expectException(InvalidArgumentException::class);

            // WHEN: Calling fromString
            PageStatus::fromString($invalidValue);
        }
    }

    /** @test */
    #[Test]
    public function should_have_exactly_two_cases(): void
    {
        // WHEN: Getting all cases
        $cases = PageStatus::cases();

        // THEN: Should have exactly 2 cases
        $this->assertCount(2, $cases);
        $this->assertContains(PageStatus::ACTIVE, $cases);
        $this->assertContains(PageStatus::PASSIVE, $cases);
    }

    /** @test */
    #[Test]
    public function should_support_cases_iteration(): void
    {
        // WHEN: Iterating through all cases
        $caseNames = [];
        $caseValues = [];

        foreach (PageStatus::cases() as $case) {
            $caseNames[] = $case->name;
            $caseValues[] = $case->value;
        }

        // THEN: Should have all cases
        $this->assertContains('ACTIVE', $caseNames);
        $this->assertContains('PASSIVE', $caseNames);
        $this->assertContains('active', $caseValues);
        $this->assertContains('passive', $caseValues);
    }

    /** @test */
    #[Test]
    public function should_map_string_to_all_cases(): void
    {
        // GIVEN: Valid string values
        $mappings = [
            'active' => PageStatus::ACTIVE,
            'passive' => PageStatus::PASSIVE,
        ];

        foreach ($mappings as $string => $expectedCase) {
            // WHEN: Creating from string
            $result = PageStatus::fromString($string);

            // THEN: Should match expected case
            $this->assertSame($expectedCase, $result);
        }
    }
}
