<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Models\User;
use Tests\Feature\FeatureTestCase;

/**
 * Test suite for mass user creation functionality
 * Tests CSV/Excel file upload, validation, and bulk user creation
 * Scoring: 35 points total
 * - mass_creation_page_loads: 5 points
 * - csv_file_upload_works: 10 points
 * - excel_file_upload_works: 5 points
 * - file_validation_prevents_invalid_formats: 5 points
 * - data_preview_before_processing: 10 points
 */

class MassUserCreationTest extends FeatureTestCase
{
    /**
     * Test that mass user creation page loads successfully
     * Scoring: 5 points
     */
    #[Test]
    #[Group('mass-creation')]
    public function mass_creation_page_loads_successfully()
    {
        $response = $this->get(route('users.mass-create'));
        
        $response->assertStatus(200);
        $response->assertViewIs('users.mass-create');
        
        // Page should contain file upload form
        $response->assertSee('name="file"', false);
        $response->assertSee('type="file"', false);
        $response->assertSee('enctype="multipart/form-data"', false);
        
        $this->fail('EXPECTED FAILURE: Mass creation route and view do not exist. Developers must create route and view for bulk user creation.');
    }

    /**
     * Test CSV file upload and processing for mass user creation
     * Scoring: 10 points
     */
    #[Test]
    #[Group('mass-creation')]
    public function csv_file_upload_works_correctly()
    {
        $usersData = $this->createMultipleValidUsers(5);
        $csvFile = $this->createValidCsvFile($usersData);
        
        $response = $this->post(route('users.mass-store'), [
            'file' => $csvFile
        ]);
        
        $response->assertStatus(201);
        
        // All users should be created in database
        $this->assertMultipleUsersCreated($usersData);
        
        // Response should contain summary
        $this->assertBulkOperationSummary($response, 5, 0);
        
        $response->assertJson([
            'message' => 'Usuarios creados exitosamente',
            'summary' => [
                'total_processed' => 5,
                'successful' => 5,
                'failed' => 0
            ]
        ]);
        
        $this->fail('EXPECTED FAILURE: Mass store route does not exist. Developers must implement mass user creation endpoint.');
    }

    /**
     * Test Excel file upload for mass user creation
     * Scoring: 5 points
     */
    #[Test]
    #[Group('mass-creation')]
    public function excel_file_upload_works_correctly()
    {
        $excelFile = $this->createValidExcelFile();
        
        $response = $this->post(route('users.mass-store'), [
            'file' => $excelFile
        ]);
        
        $response->assertStatus(201);
        
        $this->fail('EXPECTED FAILURE: Excel file processing not implemented. Developers must add support for .xlsx files.');
    }

    /**
     * Test that invalid file formats are rejected
     * Scoring: 5 points
     */
    #[Test]
    #[Group('mass-creation')]
    public function file_validation_prevents_invalid_formats()
    {
        $invalidFile = $this->createInvalidFileFormat();
        
        $response = $this->post(route('users.mass-store'), [
            'file' => $invalidFile
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
        
        $this->assertValidationErrorContains('formato de archivo no válido', $response->json('errors'));
        
        $this->fail('EXPECTED FAILURE: File format validation not implemented. Only CSV and Excel files should be accepted.');
    }

    /**
     * Test data preview functionality before processing
     */
    #[Test]
    #[Group("mass-creation")]
    public function data_preview_before_processing_works()
    {
        $usersData = $this->createMultipleValidUsers(3);
        $csvFile = $this->createValidCsvFile($usersData);
        
        // First, upload file for preview
        $response = $this->post(route('users.mass-preview'), [
            'file' => $csvFile
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'preview_data' => [
                '*' => ['name', 'phone', 'dni']
            ],
            'validation_summary' => [
                'total_rows',
                'valid_rows',
                'invalid_rows',
                'errors'
            ]
        ]);
        
        // Preview should show the data without creating users
        $this->assertEquals(0, User::count(), 'Preview should not create users in database');
        
        $this->fail('EXPECTED FAILURE: Preview functionality not implemented. Developers must create preview endpoint.');
    }

    /**
     * Test that validation errors are shown for invalid CSV data
     */
    #[Test]
    #[Group("mass-creation")]
    public function invalid_csv_data_shows_validation_errors()
    {
        $invalidFile = $this->createInvalidCsvFile();
        
        $response = $this->post(route('users.mass-preview'), [
            'file' => $invalidFile
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'validation_summary' => [
                'total_rows',
                'valid_rows', 
                'invalid_rows',
                'errors' => [
                    '*' => ['row', 'field', 'message']
                ]
            ]
        ]);
        
        $summary = $response->json('validation_summary');
        $this->assertGreaterThan(0, $summary['invalid_rows']);
        
        $this->fail('EXPECTED FAILURE: CSV data validation not implemented.');
    }

    /**
     * Test that duplicate phone numbers in CSV are detected
     */
    #[Test]
    #[Group("mass-creation")]
    public function duplicate_phones_in_csv_are_detected()
    {
        $duplicateFile = $this->createCsvFileWithDuplicates();
        
        $response = $this->post(route('users.mass-preview'), [
            'file' => $duplicateFile
        ]);
        
        $response->assertStatus(200);
        
        $summary = $response->json('validation_summary');
        $this->assertGreaterThan(0, $summary['invalid_rows']);
        
        // Should detect duplicate phone numbers
        $errors = $response->json('validation_summary.errors');
        $duplicateErrors = collect($errors)->filter(function ($error) {
            return str_contains($error['message'], 'duplicado');
        });
        
        $this->assertGreaterThan(0, $summary['invalid_rows']);
        
        $this->fail('EXPECTED FAILURE: Duplicate detection within CSV file not implemented.');
    }

    /**
     * Test CSV template download functionality
     */
    #[Test]
    #[Group("mass-creation")]
    public function csv_template_can_be_downloaded()
    {
        $response = $this->get(route('users.csv-template'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition', 'attachment; filename="users_template.csv"');
        
        // Template should contain correct headers
        $content = $response->getContent();
        $this->assertStringContainsString('name,phone,dni', $content);
        
        $this->fail('EXPECTED FAILURE: CSV template download not implemented.');
    }

    /**
     * Test file size limits are enforced
     */
    #[Test]
    #[Group("mass-creation")]
    public function file_size_limits_are_enforced()
    {
        $oversizedFile = $this->createOversizedFile();
        
        $response = $this->post(route('users.mass-store'), [
            'file' => $oversizedFile
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
        
        $this->assertValidationErrorContains('archivo demasiado grande', $response->json('errors'));
        
        $this->fail('EXPECTED FAILURE: File size validation not implemented.');
    }
}