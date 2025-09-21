<?php

namespace Tests\Traits;

use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Trait for creating test users with various scenarios
 * Follows Single Responsibility Principle - focused only on user creation
 */
trait CreatesTestUsers
{
    /**
     * Create a valid user with all required fields
     */
    protected function createValidUser(array $attributes = []): array
    {
        return array_merge([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'phone' => '+1234567890',
            'dni' => '12345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $attributes);
    }

    /**
     * Create user data with invalid phone format
     */
    protected function createUserWithInvalidPhone(array $attributes = []): array
    {
        return array_merge($this->createValidUser(), [
            'phone' => 'invalid-phone',
        ], $attributes);
    }

    /**
     * Create user data with duplicate phone
     */
    protected function createUserWithDuplicatePhone(string $existingPhone): array
    {
        return array_merge($this->createValidUser(), [
            'phone' => $existingPhone,
        ]);
    }

    /**
     * Create user data with invalid DNI format
     */
    protected function createUserWithInvalidDni(array $attributes = []): array
    {
        return array_merge($this->createValidUser(), [
            'dni' => 'invalid-dni',
        ], $attributes);
    }

    /**
     * Create multiple valid users for mass operations
     */
    protected function createMultipleValidUsers(int $count = 3): array
    {
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $users[] = [
                'name' => "Usuario Test {$i}",
                'email' => "test{$i}@example.com",
                'phone' => "+123456789{$i}",
                'dni' => "1234567{$i}",
            ];
        }
        return $users;
    }

    /**
     * Create a user in the database for testing
     */
    protected function createUserInDatabase(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Create multiple users in the database
     */
    protected function createMultipleUsersInDatabase(int $count = 3): \Illuminate\Database\Eloquent\Collection
    {
        return User::factory()->count($count)->create();
    }
}