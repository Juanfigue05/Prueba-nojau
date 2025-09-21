<?php

namespace Tests\Contracts;

/**
 * Interface for classes that validate user data
 * Follows Interface Segregation Principle
 */
interface ValidatesUserData
{
    /**
     * Validate user data according to business rules
     */
    public function validateUserData(array $userData): array;

    /**
     * Validate phone number format and uniqueness
     */
    public function validatePhoneNumber(string $phone): bool;

    /**
     * Validate DNI format
     */
    public function validateDni(string $dni): bool;
}