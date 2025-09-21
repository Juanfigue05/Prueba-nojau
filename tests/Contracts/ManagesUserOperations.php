<?php

namespace Tests\Contracts;

/**
 * Interface for classes that manage user operations
 * Follows Interface Segregation Principle
 */
interface ManagesUserOperations
{
    /**
     * Create a single user
     */
    public function createUser(array $userData): array;

    /**
     * Create multiple users in bulk
     */
    public function createMultipleUsers(array $usersData): array;

    /**
     * Delete multiple users by IDs
     */
    public function deleteMultipleUsers(array $userIds): array;

    /**
     * Get user creation statistics
     */
    public function getCreationStatistics(): array;
}