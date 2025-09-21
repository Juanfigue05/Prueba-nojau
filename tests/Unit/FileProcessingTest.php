<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;
use Tests\Traits\HandlesFileUploads;
use Tests\Traits\AssertsUserData;
use Tests\Contracts\ProcessesFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test suite for file processing functionality
 * Tests CSV/Excel parsing, template generation, and file validation
 * 
 * Scoring: 20 points total
 * - csv_parsing_works_correctly: 8 points
 * - excel_parsing_works_correctly: 4 points
 * - file_format_validation: 4 points
 * - csv_template_generation: 4 points
 */
class FileProcessingTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers, HandlesFileUploads, AssertsUserData;
    private $fileProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->setupFileStorage();
        $this->fileProcessor = $this->getFileProcessor();
    }

    protected function tearDown(): void
    {
        $this->cleanupFiles();
        parent::tearDown();
    }

    /**
     * Get file processor instance - this should be implemented by developers
     */
    protected function getFileProcessor(): ProcessesFiles
    {
        // This will fail until developers create a file processing service
        $this->fail('EXPECTED FAILURE: Developers must create a FileProcessingService that implements ProcessesFiles interface.');
    }

    /**
     * Test CSV file parsing functionality
     * 
     * Scoring: 8 points
     */
    #[Test]
    #[Group('file-processing')]
    public function csv_parsing_works_correctly()
    {
        $processor = $this->getFileProcessor();

        // Test with valid CSV data
        $usersData = $this->createMultipleValidUsers(3);
        $csvFile = $this->createValidCsvFile($usersData);

        $parsedData = $processor->processUploadedFile($csvFile);

        $this->assertCount(3, $parsedData);
        $this->assertEquals($usersData[0]['name'], $parsedData[0]['name']);
        $this->assertEquals($usersData[0]['phone'], $parsedData[0]['phone']);
        $this->assertEquals($usersData[0]['dni'], $parsedData[0]['dni']);

        // Test with CSV containing different encodings
        $csvWithAccents = $this->createCsvWithSpecialCharacters();
        $parsedAccents = $processor->processUploadedFile($csvWithAccents);
        
        $this->assertStringContainsString('ñ', $parsedAccents[0]['name']);
        $this->assertStringContainsString('é', $parsedAccents[0]['name']);

        // Test with CSV containing empty rows
        $csvWithEmptyRows = $this->createCsvWithEmptyRows();
        $parsedWithEmpty = $processor->processUploadedFile($csvWithEmptyRows);
        
        // Should skip empty rows
        $this->assertCount(2, $parsedWithEmpty); // Only non-empty rows

        $this->fail('EXPECTED FAILURE: CSV parsing service not implemented.');
    }

    /**
     * Test Excel file parsing functionality
     * 
     * Scoring: 4 points
     */
    #[Test]
    #[Group('file-processing')]
    public function excel_parsing_works_correctly()
    {
        $processor = $this->getFileProcessor();

        $excelFile = $this->createValidExcelFile();
        $parsedData = $processor->processUploadedFile($excelFile);

        $this->assertIsArray($parsedData);
        $this->assertGreaterThan(0, count($parsedData));

        // Each row should have required fields
        foreach ($parsedData as $row) {
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayHasKey('phone', $row);
            $this->assertArrayHasKey('dni', $row);
        }

        $this->fail('EXPECTED FAILURE: Excel parsing service not implemented.');
    }

    /**
     * Test file format validation
     * 
     * Scoring: 4 points
     */
    #[Test]
    #[Group('file-processing')]
    public function file_format_validation_works()
    {
        $processor = $this->getFileProcessor();

        // Valid formats should pass
        $csvFile = $this->createValidCsvFile();
        $this->assertTrue(
            $processor->validateFileFormat($csvFile),
            'CSV file should be valid format'
        );

        $excelFile = $this->createValidExcelFile();
        $this->assertTrue(
            $processor->validateFileFormat($excelFile),
            'Excel file should be valid format'
        );

        // Invalid formats should fail
        $textFile = $this->createInvalidFileFormat();
        $this->assertFalse(
            $processor->validateFileFormat($textFile),
            'Text file should be invalid format'
        );

        // Oversized files should fail
        $oversizedFile = $this->createOversizedFile();
        $this->assertFalse(
            $processor->validateFileFormat($oversizedFile),
            'Oversized file should be invalid'
        );

        $this->fail('EXPECTED FAILURE: File format validation not implemented.');
    }

    /**
     * Test CSV template generation
     * 
     * Scoring: 4 points
     */
    #[Test]
    #[Group('file-processing')]
    public function csv_template_generation_works()
    {
        $processor = $this->getFileProcessor();

        $template = $processor->generateCsvTemplate();

        // Template should contain proper headers
        $this->assertStringContainsString('name,phone,dni', $template);

        // Should contain example data
        $lines = explode("\n", $template);
        $this->assertGreaterThan(1, count($lines)); // Header + at least one example

        // Example data should follow proper format
        $exampleLine = $lines[1];
        $fields = str_getcsv($exampleLine);
        $this->assertCount(3, $fields);
        $this->assertValidPhoneFormat($fields[1]);
        $this->assertValidDniFormat($fields[2]);

        $this->fail('EXPECTED FAILURE: CSV template generation not implemented.');
    }

    /**
     * Test error handling for malformed CSV files
     * 
     * Bonus points test
     */
    #[Test]
    #[Group('file-processing')]
    public function malformed_csv_error_handling()
    {
        $processor = $this->getFileProcessor();

        $malformedCsv = $this->createMalformedCsvFile();

        try {
            $parsedData = $processor->processUploadedFile($malformedCsv);
            // Should either parse what it can or throw a descriptive exception
            $this->assertIsArray($parsedData);
        } catch (\Exception $e) {
            $this->assertStringContainsString('formato', strtolower($e->getMessage()));
        }

        $this->fail('EXPECTED FAILURE: Malformed CSV error handling not implemented.');
    }

    /**
     * Test large file processing performance
     * 
     * Bonus performance test
     */
    #[Test]
    #[Group('file-processing')]
    public function large_file_processing_performance()
    {
        $processor = $this->getFileProcessor();

        $largeCsv = $this->createLargeCsvFile(1000); // 1000 users

        $startTime = microtime(true);
        $parsedData = $processor->processUploadedFile($largeCsv);
        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        $this->assertLessThan(5.0, $executionTime, 'Large file processing should complete within 5 seconds');
        $this->assertCount(1000, $parsedData);

        $this->fail('EXPECTED FAILURE: Large file processing optimization not implemented.');
    }

    /**
     * Helper method to create CSV with special characters
     */
    private function createCsvWithSpecialCharacters(): UploadedFile
    {
        $csvContent = "name,phone,dni\n";
        $csvContent .= "José María Peña,+1111111111,11111111\n";
        $csvContent .= "María Fernández,+2222222222,22222222\n";

        return UploadedFile::fake()->createWithContent(
            'users_special.csv',
            $csvContent
        );
    }

    /**
     * Helper method to create CSV with empty rows
     */
    private function createCsvWithEmptyRows(): UploadedFile
    {
        $csvContent = "name,phone,dni\n";
        $csvContent .= "Usuario 1,+1111111111,11111111\n";
        $csvContent .= ",,\n"; // Empty row
        $csvContent .= "Usuario 2,+2222222222,22222222\n";
        $csvContent .= "\n"; // Another empty row

        return UploadedFile::fake()->createWithContent(
            'users_empty.csv',
            $csvContent
        );
    }

    /**
     * Helper method to create malformed CSV
     */
    private function createMalformedCsvFile(): UploadedFile
    {
        $csvContent = "name,phone,dni\n";
        $csvContent .= "Usuario 1,+1111111111,11111111\n";
        $csvContent .= "Usuario 2,+2222222222\n"; // Missing field
        $csvContent .= "Usuario 3,+3333333333,33333333,extra_field\n"; // Extra field

        return UploadedFile::fake()->createWithContent(
            'users_malformed.csv',
            $csvContent
        );
    }

    /**
     * Helper method to create large CSV file
     */
    private function createLargeCsvFile(int $userCount): UploadedFile
    {
        $csvContent = "name,phone,dni\n";
        
        for ($i = 1; $i <= $userCount; $i++) {
            $phone = '+123456' . str_pad($i, 4, '0', STR_PAD_LEFT);
            $dni = str_pad($i, 8, '0', STR_PAD_LEFT);
            $csvContent .= "Usuario {$i},{$phone},{$dni}\n";
        }

        return UploadedFile::fake()->createWithContent(
            'large_users.csv',
            $csvContent
        );
    }
}