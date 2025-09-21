<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Models\User;
use Tests\FeatureTestCase;
use Illuminate\Support\Facades\DB;

/**
 * Advanced test suite for user validation functionality
 * Tests international formats, database duplicates, and business rules
 * Scoring: 35 points total
 * - international_phone_validation: 10 points
 * - advanced_dni_validation: 10 points
 * - cross_database_duplicate_detection: 10 points
 * - business_rules_validation: 5 points
 */
#[Group('user-validation-advanced')]
class UserValidationAdvancedTest extends FeatureTestCase
{
    /**
     * Test international phone number validation
     * Scoring: 10 points
     */
    #[Test]
    public function international_phone_validation_works_comprehensively()
    {
        $validInternationalPhones = [
            '+34123456789',     // Spain
            '+51987654321',     // Peru
            '+1234567890',      // US/Canada
            '+49123456789',     // Germany
            '+86123456789012',  // China (longer)
            '+447123456789',    // UK
            '+33123456789',     // France
            '+39123456789',     // Italy
            '+55123456789',     // Brazil
            '+7123456789',      // Russia
        ];
        
        foreach ($validInternationalPhones as $phone) {
            $userData = $this->createValidUserData();
            $userData['phone'] = $phone;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(201, "Phone {$phone} should be valid");
            
            $this->assertDatabaseHas('users', [
                'phone' => $phone
            ]);
        }
        
        $this->fail('EXPECTED FAILURE: International phone validation not implemented. Must support major international formats.');
    }

    /**
     * Test advanced DNI validation with checksum
     * Scoring: 10 points
     */
    #[Test]
    public function advanced_dni_validation_with_checksum()
    {
        $validDnisWithChecksum = [
            '12345678Z', // Valid Spanish DNI with checksum
            '87654321X',
            '11223344G',
            '99887766Y',
            '55443322N',
        ];
        
        $invalidDnisWithChecksum = [
            '12345678A', // Invalid checksum (should be Z)
            '87654321B', // Invalid checksum (should be X)
            '11223344H', // Invalid checksum (should be G)
        ];
        
        // Test valid DNIs
        foreach ($validDnisWithChecksum as $dni) {
            $userData = $this->createValidUserData();
            $userData['dni'] = $dni;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(201, "DNI {$dni} with valid checksum should be accepted");
        }
        
        // Test invalid DNIs
        foreach ($invalidDnisWithChecksum as $dni) {
            $userData = $this->createValidUserData();
            $userData['dni'] = $dni;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(422, "DNI {$dni} with invalid checksum should be rejected");
            $response->assertJsonValidationErrors(['dni']);
            $this->assertValidationErrorContains('checksum del DNI no es válido', $response->json('errors'));
        }
        
        $this->fail('EXPECTED FAILURE: DNI checksum validation not implemented. Must validate Spanish DNI checksum algorithm.');
    }

    /**
     * Test cross-database duplicate detection
     * Scoring: 10 points
     */
    #[Test]
    public function cross_database_duplicate_detection_works()
    {
        // Create users in database using the model creation method
        $existingUsers = $this->createMultipleUsers(5);
        
        // Test individual creation duplicate detection
        $duplicateUserData = $this->createValidUserData();
        $duplicateUserData['phone'] = $existingUsers[0]->phone;
        
        $response = $this->post(route('users.store'), $duplicateUserData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
        
        // Test mass creation duplicate detection
        $massData = [
            $this->createValidUserData(),
            ['name' => 'Duplicate Phone', 'phone' => $existingUsers->get(1)->phone, 'dni' => '11111111A'],
            ['name' => 'Duplicate DNI', 'phone' => '999888777', 'dni' => $existingUsers->get(2)->dni],
            $this->createValidUserData(),
        ];
        
        $csvFile = $this->createValidCsvFile($massData);
        
        $response = $this->post(route('users.mass-preview'), [
            'file' => $csvFile
        ]);
        
        $response->assertStatus(200);
        
        $validationSummary = $response->json('validation_summary');
        $this->assertEquals(4, $validationSummary['total_rows']);
        $this->assertEquals(2, $validationSummary['valid_rows']); // Only first and last
        $this->assertEquals(2, $validationSummary['invalid_rows']); // Duplicates
        
        $errors = $response->json('validation_summary.errors');
        $duplicateErrors = collect($errors)->where('type', 'database_duplicate');
        $this->assertCount(2, $duplicateErrors);
        
        $this->fail('EXPECTED FAILURE: Cross-database duplicate detection not implemented. Must check against existing records.');
    }

    /**
     * Test business rules validation
     * Scoring: 5 points
     */
    #[Test]
    public function business_rules_validation_works()
    {
        // Business rule: No more than 100 users per day
        $this->createMultipleValidUsers(99); // Create 99 users today
        
        $userData = $this->createValidUserData();
        
        $response = $this->post(route('users.store'), $userData);
        
        $response->assertStatus(201); // 100th user should be OK
        
        // 101st user should be rejected
        $extraUserData = $this->createValidUserData();
        
        $response = $this->post(route('users.store'), $extraUserData);
        
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Límite diario de creación de usuarios alcanzado (100 usuarios por día)'
        ]);
        
        $this->fail('EXPECTED FAILURE: Business rules validation not implemented. Must enforce daily creation limits.');
    }

    /**
     * Test phone number normalization and variants
     */
    #[Test]
    public function phone_normalization_handles_variants()
    {
        $phoneVariants = [
            ['input' => '123 456 789', 'normalized' => '123456789'],
            ['input' => '123-456-789', 'normalized' => '123456789'],
            ['input' => '(123) 456-789', 'normalized' => '123456789'],
            ['input' => '+34 123 456 789', 'normalized' => '+34123456789'],
            ['input' => '+34-123-456-789', 'normalized' => '+34123456789'],
            ['input' => '0034 123 456 789', 'normalized' => '+34123456789'],
        ];
        
        foreach ($phoneVariants as $variant) {
            $userData = $this->createValidUserData();
            $userData['phone'] = $variant['input'];
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(201);
            
            // Check that phone was normalized in database
            $this->assertDatabaseHas('users', [
                'phone' => $variant['normalized']
            ]);
        }
        
        $this->fail('EXPECTED FAILURE: Phone normalization not implemented. Must normalize various phone formats.');
    }

    /**
     * Test DNI format variations
     */
    #[Test]
    public function dni_format_variations_handling()
    {
        $dniVariants = [
            ['input' => '12345678-Z', 'normalized' => '12345678Z'],
            ['input' => '12.345.678-Z', 'normalized' => '12345678Z'],
            ['input' => '12 345 678 Z', 'normalized' => '12345678Z'],
            ['input' => '12345678z', 'normalized' => '12345678Z'], // lowercase
        ];
        
        foreach ($dniVariants as $variant) {
            $userData = $this->createValidUserData();
            $userData['dni'] = $variant['input'];
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(201);
            
            // Check that DNI was normalized in database
            $this->assertDatabaseHas('users', [
                'dni' => $variant['normalized']
            ]);
        }
        
        $this->fail('EXPECTED FAILURE: DNI normalization not implemented. Must normalize various DNI formats.');
    }

    /**
     * Test name validation with special characters
     */
    #[Test]
    public function name_validation_with_special_characters()
    {
        $validNames = [
            'José María',
            'María José de la Cruz',
            'Jean-Pierre',
            'O\'Connor',
            'Müller',
            'Château',
            'Ñoño',
            'José Ángel',
        ];
        
        foreach ($validNames as $name) {
            $userData = $this->createValidUserData();
            $userData['name'] = $name;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(201, "Name '{$name}' should be valid");
        }
        
        $invalidNames = [
            'User123',      // Numbers not allowed
            'User@domain',  // Email symbols not allowed
            'User#123',     // Hash symbols not allowed
            'User&Co',      // Ampersand not allowed
        ];
        
        foreach ($invalidNames as $name) {
            $userData = $this->createValidUserData();
            $userData['name'] = $name;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(422, "Name '{$name}' should be invalid");
            $response->assertJsonValidationErrors(['name']);
        }
        
        $this->fail('EXPECTED FAILURE: Advanced name validation not implemented. Must handle international characters properly.');
    }

    /**
     * Test concurrent duplicate detection
     */
    #[Test]
    public function concurrent_duplicate_detection_works()
    {
        $userData = $this->createValidUserData();
        
        // Simulate concurrent requests with same data
        DB::beginTransaction();
        
        try {
            $response1 = $this->post(route('users.store'), $userData);
            $response2 = $this->post(route('users.store'), $userData);
            
            // One should succeed, one should fail
            $responses = [$response1, $response2];
            $successCount = collect($responses)->filter(fn($r) => $r->status() === 201)->count();
            $failureCount = collect($responses)->filter(fn($r) => $r->status() === 422)->count();
            
            $this->assertEquals(1, $successCount, 'Only one request should succeed');
            $this->assertEquals(1, $failureCount, 'One request should fail due to duplicate');
            
            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        $this->fail('EXPECTED FAILURE: Concurrent duplicate detection not implemented. Must handle race conditions.');
    }

    /**
     * Test validation error localization
     */
    #[Test]
    public function validation_error_localization_works()
    {
        app()->setLocale('es');
        
        $response = $this->post(route('users.store'), []);
        
        $response->assertStatus(422);
        
        $errors = $response->json('errors');
        
        // Errors should be in Spanish
        $this->assertStringContainsString('requerido', implode(' ', $errors['name'] ?? []));
        $this->assertStringContainsString('teléfono', implode(' ', $errors['phone'] ?? []));
        
        // Test English locale
        app()->setLocale('en');
        
        $response = $this->post(route('users.store'), []);
        
        $response->assertStatus(422);
        
        $errors = $response->json('errors');
        
        // Errors should be in English
        $this->assertStringContainsString('required', implode(' ', $errors['name'] ?? []));
        $this->assertStringContainsString('phone', implode(' ', $errors['phone'] ?? []));
        
        $this->fail('EXPECTED FAILURE: Validation error localization not implemented.');
    }

    /**
     * Test performance with large duplicate detection
     */
    #[Test]
    public function performance_with_large_duplicate_detection()
    {
        // Create many users in database
        $users = $this->createMultipleUsers(100); // Reduced for performance
        
        $startTime = microtime(true);
        
        // Test duplicate detection performance
        $existingUser = $users[0];
        $duplicateData = $this->createValidUserData();
        $duplicateData['phone'] = $existingUser->phone;
        
        $response = $this->post(route('users.store'), $duplicateData);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Should complete within reasonable time (< 1 second)
        $this->assertLessThan(1.0, $executionTime, 'Duplicate detection should be fast even with many records');
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
        
        $this->fail('EXPECTED FAILURE: Optimized duplicate detection not implemented. Must use database indexes.');
    }
}