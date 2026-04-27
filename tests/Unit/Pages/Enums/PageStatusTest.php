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
    public function shouldHaveActiveCase(): void
    {
        // WHEN: Accessing ACTIVE case
        $status = PageStatus::ACTIVE;

        // THEN: Should be a valid PageStatus enum
        $this->assertInstanceOf(PageStatus::class, $status);
        $this->assertEquals('active', $status->value);
    }

    /** @test */
    #[Test]
    public function shouldHavePassiveCase(): void
    {
        // WHEN: Accessing PASSIVE case
        $status = PageStatus::PASSIVE;

        // THEN: Should be a valid PageStatus enum
        $this->assertInstanceOf(PageStatus::class, $status);
        $this->assertEquals('passive', $status->value);
    }

    /** @test */
    #[Test]
    public function shouldHaveCorrectValues(): void
    {
        // THEN: Enum values should be correct strings
        $this->assertEquals('active', PageStatus::ACTIVE->value);
        $this->assertEquals('passive', PageStatus::PASSIVE->value);
    }

    /** @test */
    #[Test]
    public function shouldCreateFromStringActive(): void
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
    public function shouldCreateFromStringPassive(): void
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
    public function shouldThrowExceptionForInvalidString(): void
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
    public function shouldThrowExceptionForUnknownStatus(): void
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
    public function shouldThrowExceptionForEmptyString(): void
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
    public function shouldThrowExceptionForCaseSensitive(): void
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
    public function shouldCompareCasesCorrectly(): void
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
    public function shouldCompareDifferentCasesCorrectly(): void
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
    public function shouldReturnStringValue(): void
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
    public function shouldHaveNameProperty(): void
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
    public function shouldSupportFromMethod(): void
    {
        // WHEN: Using from() method with value
        $status = PageStatus::from('active');

        // THEN: Should return ACTIVE case
        $this->assertEquals(PageStatus::ACTIVE, $status);
    }

    /** @test */
    #[Test]
    public function shouldThrowExceptionFromMethodWithInvalidValue(): void
    {
        // THEN: Should throw ValueError (built-in enum behavior)
        $this->expectException(\ValueError::class);

        // WHEN: Using from() with invalid value
        PageStatus::from('invalid');
    }

    /** @test */
    #[Test]
    public function shouldSupportTryFromMethod(): void
    {
        // WHEN: Using tryFrom() method with valid value
        $status = PageStatus::tryFrom('active');

        // THEN: Should return ACTIVE case
        $this->assertEquals(PageStatus::ACTIVE, $status);
    }

    /** @test */
    #[Test]
    public function shouldReturnNullFromTryFromWithInvalidValue(): void
    {
        // WHEN: Using tryFrom() with invalid value
        $status = PageStatus::tryFrom('invalid');

        // THEN: Should return null
        $this->assertNull($status);
    }

    /** @test */
    #[Test]
    public function shouldBeCastableToString(): void
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
    public function shouldHandleMultipleFromStringCalls(): void
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
    public function shouldBeUsableInSwitchStatement(): void
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
    public function shouldBeComparableWithValue(): void
    {
        // GIVEN: PageStatus enum
        $status = PageStatus::ACTIVE;

        // THEN: Value can be compared directly
        $this->assertTrue($status->value === 'active');
        $this->assertFalse($status->value === 'passive');
    }

    /** @test */
    #[Test]
    public function shouldSupportInArray(): void
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
    public function shouldSupportArrayAccess(): void
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
    public function shouldBeSerializable(): void
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
    public function shouldHandleFromStringWithWhitespace(): void
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
    public function shouldHaveExactlyTwoCases(): void
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
    public function shouldSupportCasesIteration(): void
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
    public function shouldMapStringToAllCases(): void
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
