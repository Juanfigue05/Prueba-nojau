<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Models\User;
use Tests\FeatureTestCase;

/**
 * Test suite for individual user creation functionality
 * Tests form validation, unique constraints, and user creation flow
 * Scoring: 25 points total
 * - user_creation_form_loads: 5 points
 * - valid_user_creation_works: 10 points
 * - phone_uniqueness_validation: 5 points
 * - dni_uniqueness_validation: 5 points
 */
#[Group('user-creation')]
class UserCreationTest extends FeatureTestCase
{
    /**
     * Test that user creation form loads successfully
     * Scoring: 5 points
     */
    #[Test]
    public function user_creation_form_loads_successfully()
    {
        $response = $this->get(route('users.create'));
        
        $response->assertStatus(200);
        $response->assertViewIs('users.create');
        
        // Form should contain required fields
        $response->assertSee('name="name"', false);
        $response->assertSee('name="phone"', false);
        $response->assertSee('name="dni"', false);
        $response->assertSee('type="submit"', false);
        
        $this->fail('EXPECTED FAILURE: User creation form not implemented. Developers must create users.create view with proper form fields.');
    }

    /**
     * Test valid user creation works correctly
     * Scoring: 10 points
     */
    #[Test]
    public function valid_user_creation_works_correctly()
    {
        $userData = $this->createValidUserData();
        
        $response = $this->post(route('users.store'), $userData);
        
        $response->assertStatus(201);
        
        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'phone' => $userData['phone'],
            'dni' => $userData['dni']
        ]);
        
        // Response should contain success message
        $response->assertJson([
            'message' => 'Usuario creado exitosamente',
            'user' => [
                'name' => $userData['name'],
                'phone' => $userData['phone'],
                'dni' => $userData['dni']
            ]
        ]);
        
        $this->fail('EXPECTED FAILURE: User creation endpoint not implemented. Developers must implement user creation logic.');
    }

    /**
     * Test phone number uniqueness validation
     * Scoring: 5 points
     */
    #[Test]
    public function phone_uniqueness_validation_works()
    {
        $existingUser = $this->createValidUser();
        
        $duplicateUserData = $this->createValidUserData();
        $duplicateUserData['phone'] = $existingUser->phone; // Use same phone
        
        $response = $this->post(route('users.store'), $duplicateUserData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
        
        $this->assertValidationErrorContains('teléfono ya está en uso', $response->json('errors'));
        
        // Verify duplicate user was not created
        $this->assertDatabaseMissing('users', [
            'name' => $duplicateUserData['name'],
            'dni' => $duplicateUserData['dni']
        ]);
        
        $this->fail('EXPECTED FAILURE: Phone uniqueness validation not implemented.');
    }

    /**
     * Test DNI uniqueness validation
     * Scoring: 5 points
     */
    #[Test]
    public function dni_uniqueness_validation_works()
    {
        $existingUser = $this->createValidUser();
        
        $duplicateUserData = $this->createValidUserData();
        $duplicateUserData['dni'] = $existingUser->dni; // Use same DNI
        
        $response = $this->post(route('users.store'), $duplicateUserData);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['dni']);
        
        $this->assertValidationErrorContains('DNI ya está en uso', $response->json('errors'));
        
        // Verify duplicate user was not created
        $this->assertDatabaseMissing('users', [
            'name' => $duplicateUserData['name'],
            'phone' => $duplicateUserData['phone']
        ]);
        
        $this->fail('EXPECTED FAILURE: DNI uniqueness validation not implemented.');
    }

    /**
     * Test required field validation
     */
    #[Test]
    public function required_field_validation_works()
    {
        $response = $this->post(route('users.store'), []);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'phone', 'dni']);
        
        $this->assertValidationErrorContains('requerido', $response->json('errors'));
        
        $this->fail('EXPECTED FAILURE: Required field validation not implemented.');
    }

    /**
     * Test phone format validation
     */
    #[Test]
    public function phone_format_validation_works()
    {
        $invalidFormats = [
            '123',           // Too short
            'abcd1234567',   // Contains letters
            '12345678901234', // Too long
            '+123',          // Too short international
            '1234-5678-9012' // Contains invalid characters
        ];
        
        foreach ($invalidFormats as $invalidPhone) {
            $userData = $this->createValidUserData();
            $userData['phone'] = $invalidPhone;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['phone']);
            
            $this->assertValidationErrorContains('formato de teléfono no es válido', $response->json('errors'));
        }
        
        $this->fail('EXPECTED FAILURE: Phone format validation not implemented.');
    }

    /**
     * Test DNI format validation
     */
    #[Test]
    public function dni_format_validation_works()
    {
        $invalidFormats = [
            '123',        // Too short
            'abcd1234',   // Contains letters
            '123456789012', // Too long
            '1234-5678',  // Contains hyphen
            '1234 5678'   // Contains space
        ];
        
        foreach ($invalidFormats as $invalidDni) {
            $userData = $this->createValidUserData();
            $userData['dni'] = $invalidDni;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['dni']);
            
            $this->assertValidationErrorContains('formato de DNI no es válido', $response->json('errors'));
        }
        
        $this->fail('EXPECTED FAILURE: DNI format validation not implemented.');
    }

    /**
     * Test name validation
     */
    #[Test]
    public function name_validation_works()
    {
        $invalidNames = [
            'A',           // Too short
            str_repeat('A', 256), // Too long
            '123456',      // Only numbers
            '!!!@#$',      // Only special characters
        ];
        
        foreach ($invalidNames as $invalidName) {
            $userData = $this->createValidUserData();
            $userData['name'] = $invalidName;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(422);
            $response->assertJsonValidationErrors(['name']);
        }
        
        $this->fail('EXPECTED FAILURE: Name validation not fully implemented.');
    }

    /**
     * Test successful user creation redirects properly
     */
    #[Test]
    public function successful_creation_redirects_properly()
    {
        $userData = $this->createValidUserData();
        
        $response = $this->post(route('users.store'), $userData);
        
        // Should redirect to users index or show page
        $response->assertRedirect();
        
        $this->assertTrue(
            $response->isRedirect(route('users.index')) || 
            str_contains($response->headers->get('Location'), '/users/'),
            'Should redirect to users index or user show page'
        );
        
        $this->fail('EXPECTED FAILURE: Redirect after creation not implemented.');
    }

    /**
     * Test user creation with international phone formats
     */
    #[Test]
    public function international_phone_formats_work()
    {
        $internationalFormats = [
            '+34123456789',    // Spain
            '+51987654321',    // Peru
            '+1234567890',     // US format
            '+49123456789',    // Germany
        ];
        
        foreach ($internationalFormats as $internationalPhone) {
            $userData = $this->createValidUserData();
            $userData['phone'] = $internationalPhone;
            
            $response = $this->post(route('users.store'), $userData);
            
            $response->assertStatus(201);
            
            $this->assertDatabaseHas('users', [
                'phone' => $internationalPhone
            ]);
        }
        
        $this->fail('EXPECTED FAILURE: International phone format support not implemented.');
    }

    /**
     * Test user creation form shows validation errors
     */
    #[Test]
    public function form_shows_validation_errors_properly()
    {
        $invalidData = [
            'name' => '',
            'phone' => 'invalid',
            'dni' => '123'
        ];
        
        $response = $this->from(route('users.create'))
                        ->post(route('users.store'), $invalidData);
        
        $response->assertRedirect(route('users.create'));
        $response->assertSessionHasErrors(['name', 'phone', 'dni']);
        
        // Follow redirect and check errors are displayed
        $response = $this->get(route('users.create'));
        $response->assertSee('error', false); // Should show error styling/messages
        
        $this->fail('EXPECTED FAILURE: Form error display not implemented.');
    }
}
