<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;
use Tests\Traits\AssertsUserData;
use Tests\Contracts\ValidatesUserData;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Abstract base class for user-related unit tests
 * Follows Open/Closed Principle - open for extension, closed for modification
 */
abstract class UserTestCase extends TestCase
{
    use RefreshDatabase, CreatesTestUsers, AssertsUserData;

    /**
     * Setup method called before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestEnvironment();
    }

    /**
     * Setup test environment for user tests
     */
    protected function setupTestEnvironment(): void
    {
        // Configure test database
        $this->artisan('migrate');
        
        // Setup any additional test data if needed
        $this->setupBaseTestData();
    }

    /**
     * Setup base test data - can be overridden by child classes
     */
    protected function setupBaseTestData(): void
    {
        // Base implementation - can be extended by child classes
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    /**
     * Cleanup test data - can be overridden by child classes
     */
    protected function cleanupTestData(): void
    {
        // Base implementation - can be extended by child classes
    }

    /**
     * Get the validator instance for testing
     * Must be implemented by child classes
     */
    abstract protected function getValidator(): ValidatesUserData;

    /**
     * Assert that a validator properly validates user data
     */
    protected function assertValidatorBehavior(ValidatesUserData $validator): void
    {
        // Test valid data
        $validUser = $this->createValidUser();
        $result = $validator->validateUserData($validUser);
        $this->assertEmpty($result, 'Valid user data should not produce validation errors');

        // Test invalid phone
        $this->assertFalse(
            $validator->validatePhoneNumber('invalid-phone'),
            'Invalid phone number should fail validation'
        );

        // Test invalid DNI
        $this->assertFalse(
            $validator->validateDni('invalid-dni'),
            'Invalid DNI should fail validation'
        );
    }
}