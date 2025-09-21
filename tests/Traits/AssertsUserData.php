<?php

namespace Tests\Traits;

use App\Models\User;

/**
 * Trait for making user data assertions in tests
 * Follows Single Responsibility Principle - focused only on assertions
 */
trait AssertsUserData
{
    /**
     * Assert that user data is valid according to business rules
     */
    protected function assertValidUserData(array $userData): void
    {
        $this->assertArrayHasKey('name', $userData, 'User data must contain name');
        $this->assertArrayHasKey('phone', $userData, 'User data must contain phone');
        $this->assertArrayHasKey('dni', $userData, 'User data must contain dni');
        
        $this->assertNotEmpty($userData['name'], 'Name cannot be empty');
        $this->assertNotEmpty($userData['phone'], 'Phone cannot be empty');
        $this->assertNotEmpty($userData['dni'], 'DNI cannot be empty');
    }

    /**
     * Assert that phone number has valid format
     */
    protected function assertValidPhoneFormat(string $phone): void
    {
        $phonePattern = '/^\+?[1-9]\d{1,14}$/';
        $this->assertMatchesRegularExpression(
            $phonePattern,
            $phone,
            "Phone number '{$phone}' does not match valid format"
        );
    }

    /**
     * Assert that DNI has valid format
     */
    protected function assertValidDniFormat(string $dni): void
    {
        $dniPattern = '/^\d{8,12}$/';
        $this->assertMatchesRegularExpression(
            $dniPattern,
            $dni,
            "DNI '{$dni}' does not match valid format (8-12 digits)"
        );
    }

    /**
     * Assert that phone number is unique in database
     */
    protected function assertPhoneIsUnique(string $phone): void
    {
        $existingUser = User::where('phone', $phone)->first();
        $this->assertNull(
            $existingUser,
            "Phone number '{$phone}' already exists in database"
        );
    }

    /**
     * Assert that user was created successfully in database
     */
    protected function assertUserCreatedInDatabase(array $userData): void
    {
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'phone' => $userData['phone'],
            'dni' => $userData['dni'],
        ]);
    }

    /**
     * Assert that user was not created in database
     */
    protected function assertUserNotCreatedInDatabase(array $userData): void
    {
        $this->assertDatabaseMissing('users', [
            'name' => $userData['name'],
            'phone' => $userData['phone'],
            'dni' => $userData['dni'],
        ]);
    }

    /**
     * Assert that multiple users were created successfully
     */
    protected function assertMultipleUsersCreated(array $usersData): void
    {
        foreach ($usersData as $userData) {
            $this->assertUserCreatedInDatabase($userData);
        }
    }

    /**
     * Assert that user was deleted from database
     */
    protected function assertUserDeletedFromDatabase(int $userId): void
    {
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    /**
     * Assert that multiple users were deleted
     */
    protected function assertMultipleUsersDeleted(array $userIds): void
    {
        foreach ($userIds as $userId) {
            $this->assertUserDeletedFromDatabase($userId);
        }
    }

    /**
     * Assert validation error message contains expected text
     */
    protected function assertValidationErrorContains(string $expectedMessage, array $errors): void
    {
        $allErrors = collect($errors)->flatten()->implode(' ');
        $this->assertStringContainsString(
            $expectedMessage,
            $allErrors,
            "Validation errors do not contain expected message: '{$expectedMessage}'"
        );
    }

    /**
     * Assert that response contains user creation success message
     */
    protected function assertUserCreationSuccess($response): void
    {
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'user' => ['id', 'name', 'phone', 'dni']
        ]);
    }

    /**
     * Assert that response contains bulk operation summary
     */
    protected function assertBulkOperationSummary($response, int $expectedSuccessCount, int $expectedErrorCount = 0): void
    {
        $response->assertJsonStructure([
            'summary' => [
                'total_processed',
                'successful',
                'failed',
                'errors'
            ]
        ]);

        $summary = $response->json('summary');
        $this->assertEquals($expectedSuccessCount, $summary['successful']);
        $this->assertEquals($expectedErrorCount, $summary['failed']);
    }
}