<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use App\Models\User;
use Tests\FeatureTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Test suite for database integrity and transaction management
 * Tests transactions, rollbacks, constraints, and data consistency
 * Scoring: 30 points total
 * - transaction_rollback_on_error: 10 points
 * - foreign_key_constraints: 5 points
 * - data_consistency_mass_operations: 10 points
 * - database_performance_optimization: 5 points
 */
#[Group('database-integrity')]
class DatabaseIntegrityTest extends FeatureTestCase
{
    /**
     * Test transaction rollback on error during mass operations
     * Scoring: 10 points
     */
    #[Test]
    public function transaction_rollback_on_error_during_mass_operations()
    {
        $initialUserCount = User::count();
        
        $mixedData = [
            ['name' => 'Valid User 1', 'phone' => '123456789', 'dni' => '12345678A'],
            ['name' => 'Valid User 2', 'phone' => '987654321', 'dni' => '87654321B'],
            ['name' => '', 'phone' => '555666777', 'dni' => '11223344C'], // Invalid: empty name
            ['name' => 'Valid User 3', 'phone' => '444555666', 'dni' => '99887766D'],
        ];
        
        $csvFile = $this->createValidCsvFile($mixedData);
        
        $response = $this->post(route('users.mass-store'), [
            'file' => $csvFile,
            'atomic_operation' => true // All or nothing
        ]);
        
        $response->assertStatus(422);
        
        // Verify no users were created due to rollback
        $this->assertEquals($initialUserCount, User::count(), 'No users should be created when transaction rolls back');
        
        // Verify error details
        $response->assertJsonStructure([
            'message',
            'failed_row',
            'validation_errors'
        ]);
        
        $this->fail('EXPECTED FAILURE: Atomic mass operations with rollback not implemented. Must use database transactions.');
    }

    /**
     * Test foreign key constraints enforcement
     * Scoring: 5 points
     */
    #[Test]
    public function foreign_key_constraints_are_enforced()
    {
        // This test assumes there might be related tables like user_profiles, user_roles, etc.
        $user = $this->createValidUser();
        
        // Create related record (simulated)
        DB::table('user_profiles')->insert([
            'user_id' => $user->id,
            'bio' => 'Test bio',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Try to delete user with related records
        $response = $this->delete(route('users.destroy', $user->id), [
            'force_delete' => true // Hard delete
        ]);
        
        if (Schema::hasTable('user_profiles')) {
            // Should fail due to foreign key constraint
            $response->assertStatus(422);
            $response->assertJson([
                'message' => 'No se puede eliminar el usuario porque tiene registros relacionados'
            ]);
            
            // User should still exist
            $this->assertDatabaseHas('users', ['id' => $user->id]);
        } else {
            // If no related tables exist, this test is automatically passed
            $this->assertTrue(true, 'No related tables found - foreign key constraint test skipped');
        }
        
        $this->fail('EXPECTED FAILURE: Foreign key constraints not properly implemented or enforced.');
    }

    /**
     * Test data consistency during mass operations
     * Scoring: 10 points
     */
    #[Test]
    public function data_consistency_during_mass_operations()
    {
        $initialCount = User::count();
        
        // Create valid data for mass insertion
        $validUsers = [];
        for ($i = 1; $i <= 50; $i++) {
            $validUsers[] = [
                'name' => "User {$i}",
                'phone' => "12345678" . str_pad($i, 2, '0', STR_PAD_LEFT),
                'dni' => "1234567" . str_pad($i, 2, '0', STR_PAD_LEFT) . 'A'
            ];
        }
        
        $csvFile = $this->createValidCsvFile($validUsers);
        
        $response = $this->post(route('users.mass-store'), [
            'file' => $csvFile,
            'batch_size' => 10
        ]);
        
        $response->assertStatus(201);
        
        // Verify all users were created
        $this->assertEquals($initialCount + 50, User::count());
        
        // Verify no duplicate phones or DNIs were created
        $phoneCount = DB::table('users')->count();
        $uniquePhoneCount = DB::table('users')->distinct('phone')->count();
        $uniqueDniCount = DB::table('users')->distinct('dni')->count();
        
        $this->assertEquals($phoneCount, $uniquePhoneCount, 'All phone numbers should be unique');
        $this->assertEquals($phoneCount, $uniqueDniCount, 'All DNI numbers should be unique');
        
        // Verify proper timestamps
        $recentUsers = User::where('created_at', '>', now()->subMinute())->get();
        $this->assertCount(50, $recentUsers, 'All 50 users should have recent creation timestamps');
        
        foreach ($recentUsers as $user) {
            $this->assertNotNull($user->created_at);
            $this->assertNotNull($user->updated_at);
            $this->assertEquals($user->created_at, $user->updated_at);
        }
        
        $this->fail('EXPECTED FAILURE: Data consistency checks not implemented for mass operations.');
    }

    /**
     * Test database performance optimization
     * Scoring: 5 points
     */
    #[Test]
    public function database_performance_optimization()
    {
        // Test that indexes exist for performance
        $indexes = DB::select("SHOW INDEX FROM users");
        $indexColumns = collect($indexes)->pluck('Column_name')->toArray();
        
        $this->assertContains('phone', $indexColumns, 'Phone column should have an index for unique constraint');
        $this->assertContains('dni', $indexColumns, 'DNI column should have an index for unique constraint');
        
        // Test query performance with large dataset
        $this->createMultipleValidUsers(100);
        
        $startTime = microtime(true);
        
        // This query should be fast due to indexing
        $duplicatePhone = User::where('phone', '123456789')->first();
        
        $endTime = microtime(true);
        $queryTime = $endTime - $startTime;
        
        $this->assertLessThan(0.1, $queryTime, 'Phone lookup should be fast with proper indexing');
        
        // Test bulk insert performance
        $bulkData = [];
        for ($i = 1; $i <= 1000; $i++) {
            $bulkData[] = [
                'name' => "Bulk User {$i}",
                'phone' => "99999" . str_pad($i, 5, '0', STR_PAD_LEFT),
                'dni' => "99999" . str_pad($i, 4, '0', STR_PAD_LEFT) . 'Z',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        $startTime = microtime(true);
        
        DB::table('users')->insert($bulkData);
        
        $endTime = microtime(true);
        $insertTime = $endTime - $startTime;
        
        $this->assertLessThan(2.0, $insertTime, 'Bulk insert of 1000 records should complete within 2 seconds');
        
        $this->fail('EXPECTED FAILURE: Database performance optimizations not implemented. Missing indexes or inefficient queries.');
    }

    /**
     * Test soft delete functionality and data integrity
     */
    #[Test]
    public function soft_delete_functionality_maintains_integrity()
    {
        $user = $this->createValidUser();
        $userId = $user->id;
        
        // Soft delete user
        $response = $this->delete(route('users.destroy', $userId));
        
        $response->assertStatus(200);
        
        // User should be soft deleted
        $this->assertSoftDeleted('users', ['id' => $userId]);
        
        // User should not appear in normal queries
        $this->assertNull(User::find($userId));
        
        // User should appear in withTrashed queries
        $this->assertNotNull(User::withTrashed()->find($userId));
        
        // Should be able to create new user with same phone/dni after soft delete
        $newUserData = [
            'name' => 'New User',
            'phone' => $user->phone,
            'dni' => $user->dni
        ];
        
        $response = $this->post(route('users.store'), $newUserData);
        
        $response->assertStatus(201);
        
        $this->fail('EXPECTED FAILURE: Soft delete functionality not properly implemented.');
    }

    /**
     * Test concurrent mass operations data integrity
     */
    #[Test]
    public function concurrent_mass_operations_maintain_integrity()
    {
        $initialCount = User::count();
        
        // Simulate concurrent mass operations
        $batch1Data = [];
        $batch2Data = [];
        
        for ($i = 1; $i <= 25; $i++) {
            $batch1Data[] = [
                'name' => "Batch1 User {$i}",
                'phone' => "111" . str_pad($i, 7, '0', STR_PAD_LEFT),
                'dni' => "111" . str_pad($i, 6, '0', STR_PAD_LEFT) . 'A'
            ];
            
            $batch2Data[] = [
                'name' => "Batch2 User {$i}",
                'phone' => "222" . str_pad($i, 7, '0', STR_PAD_LEFT),
                'dni' => "222" . str_pad($i, 6, '0', STR_PAD_LEFT) . 'B'
            ];
        }
        
        $csvFile1 = $this->createValidCsvFile($batch1Data);
        $csvFile2 = $this->createValidCsvFile($batch2Data);
        
        // Start both operations
        $response1 = $this->post(route('users.mass-store'), ['file' => $csvFile1]);
        $response2 = $this->post(route('users.mass-store'), ['file' => $csvFile2]);
        
        $response1->assertStatus(201);
        $response2->assertStatus(201);
        
        // Verify correct total count
        $this->assertEquals($initialCount + 50, User::count());
        
        // Verify no duplicates were created
        $phoneCount = User::count();
        $uniquePhoneCount = User::distinct('phone')->count();
        $uniqueDniCount = User::distinct('dni')->count();
        
        $this->assertEquals($phoneCount, $uniquePhoneCount);
        $this->assertEquals($phoneCount, $uniqueDniCount);
        
        $this->fail('EXPECTED FAILURE: Concurrent operation integrity not maintained. Need proper locking/isolation.');
    }

    /**
     * Test database connection handling and recovery
     */
    #[Test]
    public function database_connection_handling_and_recovery()
    {
        $userData = $this->createValidUserData();
        
        // Test normal operation first
        $response = $this->post(route('users.store'), $userData);
        $response->assertStatus(201);
        
        // Simulate connection issue (this is a mock test)
        // In real scenarios, this would test connection pooling and recovery
        try {
            DB::connection()->getPdo();
            $connectionWorking = true;
        } catch (\Exception $e) {
            $connectionWorking = false;
        }
        
        $this->assertTrue($connectionWorking, 'Database connection should be working');
        
        // Test transaction isolation levels
        $isolationLevel = DB::select("SELECT @@transaction_isolation as level")[0]->level ?? 'REPEATABLE-READ';
        
        $this->assertContains($isolationLevel, [
            'READ-UNCOMMITTED',
            'READ-COMMITTED', 
            'REPEATABLE-READ',
            'SERIALIZABLE'
        ], 'Should have a valid transaction isolation level');
        
        $this->fail('EXPECTED FAILURE: Database connection handling and recovery mechanisms not tested.');
    }

    /**
     * Test data validation at database level
     */
    #[Test]
    public function database_level_validation_constraints()
    {
        try {
            // Try to insert invalid data directly at DB level
            DB::table('users')->insert([
                'name' => null, // Should fail NOT NULL constraint
                'phone' => '123456789',
                'dni' => '12345678A',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->fail('Should have thrown exception for NULL name');
        } catch (\Exception $e) {
            $this->assertStringContainsString('cannot be null', $e->getMessage());
        }
        
        try {
            // Try to insert duplicate phone
            $existingUser = $this->createValidUser();
            
            DB::table('users')->insert([
                'name' => 'Duplicate Phone User',
                'phone' => $existingUser->phone, // Should fail UNIQUE constraint
                'dni' => '99999999Z',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->fail('Should have thrown exception for duplicate phone');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Duplicate entry', $e->getMessage());
        }
        
        $this->fail('EXPECTED FAILURE: Database-level constraints not properly implemented.');
    }

    /**
     * Test backup and restore data integrity
     */
    #[Test]
    public function backup_restore_data_integrity()
    {
        $users = $this->createMultipleValidUsers(10);
        $originalCount = User::count();
        $originalData = User::all()->toArray();
        
        // Simulate backup (export data)
        $backupData = DB::table('users')->get()->toArray();
        
        // Verify backup contains all data
        $this->assertCount($originalCount, $backupData);
        
        // Simulate restore scenario by comparing data
        foreach ($users as $user) {
            $backupUser = collect($backupData)->firstWhere('id', $user->id);
            $this->assertNotNull($backupUser, "User {$user->id} should exist in backup");
            $this->assertEquals($user->name, $backupUser->name);
            $this->assertEquals($user->phone, $backupUser->phone);
            $this->assertEquals($user->dni, $backupUser->dni);
        }
        
        $this->fail('EXPECTED FAILURE: Backup/restore data integrity procedures not implemented.');
    }
}