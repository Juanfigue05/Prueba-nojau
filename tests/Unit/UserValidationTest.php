<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UserTestCase;
use Tests\Contracts\ValidatesUserData;

/**
 * Test suite for user data validation logic
 * Tests phone validation, DNI validation, and data formatting
 * 
 * Scoring: 20 points total
 * - phone_validation_works_correctly: 5 points
 * - dni_validation_works_correctly: 5 points
 * - unique_phone_validation: 5 points
 * - user_data_formatting: 5 points
 */
class UserValidationTest extends UserTestCase
{
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = $this->getValidator();
    }

    /**
     * Get validator instance - this should be implemented by developers
     */
    protected function getValidator(): ValidatesUserData
    {
        // This will fail until developers create a validation service
        $this->fail('EXPECTED FAILURE: Developers must create a UserValidationService that implements ValidatesUserData interface.');
    }

    /**
     * Test phone number validation with various formats
     * 
     * Scoring: 5 points
     */
    #[Test]
    #[Group('validation')]
    public function phone_validation_works_correctly()
    {
        $validator = $this->getValidator();

        // Valid phone formats
        $validPhones = [
            '+1234567890',
            '+541141234567',
            '1234567890',
            '+34912345678'
        ];

        foreach ($validPhones as $phone) {
            $this->assertTrue(
                $validator->validatePhoneNumber($phone),
                "Phone '{$phone}' should be valid"
            );
        }

        // Invalid phone formats
        $invalidPhones = [
            'invalid-phone',
            '12345',
            '+',
            'abc123',
            '++1234567890',
            '',
            '12345678901234567890' // Too long
        ];

        foreach ($invalidPhones as $phone) {
            $this->assertFalse(
                $validator->validatePhoneNumber($phone),
                "Phone '{$phone}' should be invalid"
            );
        }

        $this->fail('EXPECTED FAILURE: Phone validation service not implemented.');
    }

    /**
     * Test DNI validation with various formats
     * 
     * Scoring: 5 points
     */
    #[Test]
    #[Group('validation')]
    public function dni_validation_works_correctly()
    {
        $validator = $this->getValidator();

        // Valid DNI formats (8-12 digits)
        $validDnis = [
            '12345678',
            '123456789',
            '1234567890',
            '12345678901',
            '123456789012'
        ];

        foreach ($validDnis as $dni) {
            $this->assertTrue(
                $validator->validateDni($dni),
                "DNI '{$dni}' should be valid"
            );
        }

        // Invalid DNI formats
        $invalidDnis = [
            '1234567',     // Too short
            '1234567890123', // Too long
            'abc12345678',   // Contains letters
            '12345-678',     // Contains special chars
            '',              // Empty
            '12.345.678'     // Contains dots
        ];

        foreach ($invalidDnis as $dni) {
            $this->assertFalse(
                $validator->validateDni($dni),
                "DNI '{$dni}' should be invalid"
            );
        }

        $this->fail('EXPECTED FAILURE: DNI validation service not implemented.');
    }

    /**
     * Test unique phone validation against database
     * 
     * Scoring: 5 points
     */
    #[Test]
    #[Group('validation')]
    public function unique_phone_validation_works()
    {
        $validator = $this->getValidator();

        // Create a user with a specific phone
        $existingUser = $this->createUserInDatabase(['phone' => '+1234567890']);

        // Try to validate the same phone number
        $userData = $this->createValidUser(['phone' => '+1234567890']);
        $errors = $validator->validateUserData($userData);

        $this->assertArrayHasKey('phone', $errors);
        $this->assertStringContainsString('ya está en uso', $errors['phone'][0]);

        // Different phone should pass validation
        $userData['phone'] = '+0987654321';
        $errors = $validator->validateUserData($userData);
        $this->assertArrayNotHasKey('phone', $errors);

        $this->fail('EXPECTED FAILURE: Unique phone validation not implemented.');
    }

    /**
     * Test user data formatting and sanitization
     * 
     * Scoring: 5 points
     */
    #[Test]
    #[Group('validation')]
    public function user_data_formatting_works_correctly()
    {
        $validator = $this->getValidator();

        // Test data with various formatting issues
        $messyUserData = [
            'name' => '  Juan Pérez  ',
            'email' => 'JUAN.PEREZ@EXAMPLE.COM',
            'phone' => ' +54 11 4123-4567 ',
            'dni' => ' 12.345.678 ',
        ];

        $formattedData = $validator->formatUserData($messyUserData);

        // Name should be trimmed and properly capitalized
        $this->assertEquals('Juan Pérez', $formattedData['name']);

        // Email should be lowercase
        $this->assertEquals('juan.perez@example.com', $formattedData['email']);

        // Phone should be cleaned (remove spaces and dashes)
        $this->assertEquals('+541141234567', $formattedData['phone']);

        // DNI should be cleaned (remove dots and spaces)
        $this->assertEquals('12345678', $formattedData['dni']);

        $this->fail('EXPECTED FAILURE: Data formatting service not implemented.');
    }

    /**
     * Test complete user validation workflow
     * 
     * Scoring: 5 points
     */
    #[Test]
    #[Group('validation')]
    public function complete_user_validation_workflow()
    {
        $validator = $this->getValidator();

        // Test with valid data
        $validUser = $this->createValidUser();
        $errors = $validator->validateUserData($validUser);
        $this->assertEmpty($errors, 'Valid user data should not produce errors');

        // Test with multiple validation errors
        $invalidUser = [
            'name' => '',
            'email' => 'invalid-email',
            'phone' => 'invalid-phone',
            'dni' => 'invalid-dni',
        ];

        $errors = $validator->validateUserData($invalidUser);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('phone', $errors);
        $this->assertArrayHasKey('dni', $errors);

        $this->fail('EXPECTED FAILURE: Complete validation workflow not implemented.');
    }

    /**
     * Test validation error messages are in Spanish
     * 
     * Bonus points test
     */
    #[Test]
    #[Group('validation')]
    public function validation_messages_are_in_spanish()
    {
        $validator = $this->getValidator();

        $invalidUser = $this->createUserWithInvalidPhone();
        $errors = $validator->validateUserData($invalidUser);

        $this->assertArrayHasKey('phone', $errors);
        
        $phoneError = $errors['phone'][0];
        
        // Error message should be in Spanish
        $spanishKeywords = ['teléfono', 'formato', 'válido', 'inválido', 'número'];
        $containsSpanish = false;
        
        foreach ($spanishKeywords as $keyword) {
            if (str_contains(strtolower($phoneError), $keyword)) {
                $containsSpanish = true;
                break;
            }
        }

        $this->assertTrue(
            $containsSpanish,
            "Validation message should be in Spanish: '{$phoneError}'"
        );

        $this->fail('EXPECTED FAILURE: Spanish validation messages not implemented.');
    }

    /**
     * Test performance with large datasets
     * 
     * Bonus performance test
     */
    #[Test]
    #[Group('validation')]
    public function validation_performance_with_large_datasets()
    {
        $validator = $this->getValidator();

        // Create a large number of users for performance testing
        $this->createMultipleUsersInDatabase(1000);

        $newUserData = $this->createValidUser(['phone' => '+9999999999']);

        $startTime = microtime(true);
        $errors = $validator->validateUserData($newUserData);
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        // Validation should complete within reasonable time (less than 1 second)
        $this->assertLessThan(1.0, $executionTime, 'Validation should be fast even with large datasets');

        $this->fail('EXPECTED FAILURE: Performance optimization not implemented.');
    }
}