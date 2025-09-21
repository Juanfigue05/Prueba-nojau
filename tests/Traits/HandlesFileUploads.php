<?php

namespace Tests\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Trait for handling file uploads in tests
 * Follows Single Responsibility Principle - focused only on file operations
 */
trait HandlesFileUploads
{
    /**
     * Create a valid CSV file for user upload
     */
    protected function createValidCsvFile(array $users = null): UploadedFile
    {
        if ($users === null) {
            $users = [
                ['name' => 'Usuario CSV 1', 'phone' => '+1111111111', 'dni' => '11111111'],
                ['name' => 'Usuario CSV 2', 'phone' => '+2222222222', 'dni' => '22222222'],
                ['name' => 'Usuario CSV 3', 'phone' => '+3333333333', 'dni' => '33333333'],
            ];
        }

        $csvContent = "name,phone,dni\n";
        foreach ($users as $user) {
            $csvContent .= "{$user['name']},{$user['phone']},{$user['dni']}\n";
        }

        return UploadedFile::fake()->createWithContent(
            'users.csv',
            $csvContent
        );
    }

    /**
     * Create a CSV file with invalid data
     */
    protected function createInvalidCsvFile(): UploadedFile
    {
        $csvContent = "name,phone,dni\n";
        $csvContent .= "Usuario Inválido,invalid-phone,invalid-dni\n";

        return UploadedFile::fake()->createWithContent(
            'invalid_users.csv',
            $csvContent
        );
    }

    /**
     * Create a CSV file with duplicate phone numbers
     */
    protected function createCsvFileWithDuplicates(): UploadedFile
    {
        $csvContent = "name,phone,dni\n";
        $csvContent .= "Usuario 1,+1234567890,11111111\n";
        $csvContent .= "Usuario 2,+1234567890,22222222\n"; // Duplicate phone

        return UploadedFile::fake()->createWithContent(
            'duplicate_users.csv',
            $csvContent
        );
    }

    /**
     * Create an invalid file format (not CSV/Excel)
     */
    protected function createInvalidFileFormat(): UploadedFile
    {
        return UploadedFile::fake()->create('users.txt', 100);
    }

    /**
     * Create a CSV file for user deletion (contains IDs)
     */
    protected function createDeletionCsvFile(array $userIds): UploadedFile
    {
        $csvContent = "id\n";
        foreach ($userIds as $id) {
            $csvContent .= "{$id}\n";
        }

        return UploadedFile::fake()->createWithContent(
            'users_to_delete.csv',
            $csvContent
        );
    }

    /**
     * Create an Excel file for user upload (simulated)
     */
    protected function createValidExcelFile(): UploadedFile
    {
        // Note: In a real implementation, this would create an actual Excel file
        // For this test, we'll simulate it with proper MIME type
        return UploadedFile::fake()->create(
            'users.xlsx',
            100,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    /**
     * Create a file that exceeds size limits
     */
    protected function createOversizedFile(): UploadedFile
    {
        return UploadedFile::fake()->create('large_users.csv', 10000); // 10MB
    }

    /**
     * Setup storage for file upload tests
     */
    protected function setupFileStorage(): void
    {
        Storage::fake('local');
    }

    /**
     * Clean up files after tests
     */
    protected function cleanupFiles(): void
    {
        Storage::disk('local')->deleteDirectory('uploads');
    }
}