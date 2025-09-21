<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;
use Tests\Traits\HandlesFileUploads;
use Tests\Traits\AssertsUserData;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Abstract base class for feature tests
 * Follows Open/Closed Principle - open for extension, closed for modification
 */
abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase, CreatesTestUsers, HandlesFileUploads, AssertsUserData;

    /**
     * Setup method called before each test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->setupFeatureTestEnvironment();
    }

    /**
     * Setup test environment for feature tests
     */
    protected function setupFeatureTestEnvironment(): void
    {
        // Configure test database
        $this->artisan('migrate');
        
        // Setup file storage for upload tests
        $this->setupFileStorage();
        
        // Setup any additional test data
        $this->setupFeatureTestData();
    }

    /**
     * Setup feature test data - can be overridden by child classes
     */
    protected function setupFeatureTestData(): void
    {
        // Base implementation - can be extended by child classes
    }

    /**
     * Cleanup after each test
     */
    protected function tearDown(): void
    {
        $this->cleanupFiles();
        $this->cleanupFeatureTestData();
        parent::tearDown();
    }

    /**
     * Cleanup feature test data - can be overridden by child classes
     */
    protected function cleanupFeatureTestData(): void
    {
        // Base implementation - can be extended by child classes
    }

    /**
     * Assert that a page loads successfully
     */
    protected function assertPageLoads(string $route, int $expectedStatus = 200): void
    {
        $response = $this->get($route);
        $response->assertStatus($expectedStatus);
    }

    /**
     * Assert that a form can be submitted successfully
     */
    protected function assertFormSubmissionWorks(string $route, array $data, int $expectedStatus = 200): void
    {
        $response = $this->post($route, $data);
        $response->assertStatus($expectedStatus);
    }

    /**
     * Assert that validation errors are properly displayed
     */
    protected function assertValidationErrorsDisplayed(string $route, array $invalidData): void
    {
        $response = $this->post($route, $invalidData);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors();
    }
}