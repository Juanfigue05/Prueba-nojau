<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

abstract class FeatureTestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    /**
     * Create valid user data for testing
     */
    protected function createValidUserData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Juan Pérez',
            'email' => 'juan.perez@example.com',
            'phone' => '+34600123456',
            'dni' => '12345678Z',
            'address' => 'Calle Mayor 123, Madrid',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ], $overrides);
    }

    /**
     * Create multiple valid users data
     */
    protected function createMultipleValidUsers(int $count = 3): array
    {
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $users[] = [
                'name' => "Usuario Test {$i}",
                'email' => "user{$i}@test.com",
                'phone' => "+3460012345{$i}",
                'dni' => "1234567{$i}Z",
                'address' => "Dirección {$i}, Ciudad Test",
                'password' => 'TestPass123!',
                'password_confirmation' => 'TestPass123!',
            ];
        }
        return $users;
    }

    /**
     * Create multiple User models in database
     */
    protected function createMultipleUsers(int $count = 3): array
    {
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $users[] = User::create([
                'name' => "Usuario DB {$i}",
                'email' => "dbuser{$i}@test.com",
                'phone' => "+3470012345{$i}",
                'dni' => "8765432{$i}A",
                'address' => "Dirección DB {$i}, Ciudad Test",
                'password' => bcrypt('TestPass123!'),
            ]);
        }
        return $users;
    }

    /**
     * Create a CSV file for testing
     */
    protected function createCSVFile(array $data, string $filename = 'test.csv'): UploadedFile
    {
        $csvContent = '';
        
        if (!empty($data)) {
            // Add header
            $csvContent .= implode(',', array_keys($data[0])) . "\n";
            
            // Add data rows
            foreach ($data as $row) {
                $csvContent .= implode(',', array_map(function($value) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }, $row)) . "\n";
            }
        }

        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];

        return new UploadedFile(
            $tempPath,
            $filename,
            'text/csv',
            null,
            true
        );
    }

    /**
     * Create an Excel file for testing (simulated as CSV with .xlsx extension)
     */
    protected function createExcelFile(array $data, string $filename = 'test.xlsx'): UploadedFile
    {
        // For simplicity, we'll create a CSV file with .xlsx extension
        // In a real implementation, you'd use PhpSpreadsheet
        $csvContent = '';
        
        if (!empty($data)) {
            // Add header
            $csvContent .= implode(',', array_keys($data[0])) . "\n";
            
            // Add data rows
            foreach ($data as $row) {
                $csvContent .= implode(',', array_map(function($value) {
                    return '"' . str_replace('"', '""', $value) . '"';
                }, $row)) . "\n";
            }
        }

        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];

        return new UploadedFile(
            $tempPath,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    /**
     * Create a large CSV file for performance testing
     */
    protected function createLargeCSVFile(int $rows = 1000): UploadedFile
    {
        $data = [];
        for ($i = 1; $i <= $rows; $i++) {
            $data[] = [
                'name' => "Usuario Masivo {$i}",
                'email' => "masivo{$i}@test.com",
                'phone' => "+34" . str_pad($i, 9, '0', STR_PAD_LEFT),
                'dni' => str_pad($i, 8, '0', STR_PAD_LEFT) . 'A',
                'address' => "Dirección Masiva {$i}, Ciudad Test",
            ];
        }
        
        return $this->createCSVFile($data, 'large_users.csv');
    }

    /**
     * Create CSV file with malformed data
     */
    protected function createMalformedCSVFile(): UploadedFile
    {
        $csvContent = "name,email,phone,dni,address\n";
        $csvContent .= "\"Juan Pérez\",\"juan@test.com\",\"+34600123456\",\"12345678Z\",\"Calle Mayor 123\"\n";
        $csvContent .= "\"María García\",\"maria@test.com\",\"invalid-phone\",\"invalid-dni\",\"Calle Menor 456\"\n";
        $csvContent .= "\"Pedro López\",\"invalid-email\",\"+34600123458\",\"87654321B\",\"Plaza Central 789\"\n";
        $csvContent .= "\"Ana Ruiz\",\"ana@test.com\",\"+34600123459\",\"11111111C\",\n"; // Missing address

        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];

        return new UploadedFile(
            $tempPath,
            'malformed.csv',
            'text/csv',
            null,
            true
        );
    }

    /**
     * Create CSV file with encoding issues
     */
    protected function createEncodingCSVFile(): UploadedFile
    {
        $csvContent = "name,email,phone,dni,address\n";
        $csvContent .= "\"José María\",\"jose@test.com\",\"+34600123456\",\"12345678Z\",\"Calle Española\"\n";
        $csvContent .= "\"François Müller\",\"francois@test.com\",\"+33123456789\",\"FR123456789\",\"Rue de la Paix\"\n";
        $csvContent .= "\"Søren Åberg\",\"soren@test.com\",\"+45123456789\",\"DK123456789\",\"København Street\"\n";

        $tempFile = tmpfile();
        fwrite($tempFile, mb_convert_encoding($csvContent, 'ISO-8859-1', 'UTF-8'));
        $tempPath = stream_get_meta_data($tempFile)['uri'];

        return new UploadedFile(
            $tempPath,
            'encoding_test.csv',
            'text/csv',
            null,
            true
        );
    }

    /**
     * Assert that validation errors contain specific keys
     */
    protected function assertValidationErrors(array $expectedErrors, $response): void
    {
        $response->assertStatus(422);
        $errors = $response->json('errors');
        
        foreach ($expectedErrors as $field) {
            $this->assertArrayHasKey($field, $errors, "Validation error for field '{$field}' was expected but not found.");
        }
    }

    /**
     * Assert that phone number is properly formatted
     */
    protected function assertPhoneFormatted(string $expected, string $actual): void
    {
        $this->assertEquals($expected, $actual, "Phone number should be formatted as: {$expected}");
    }

    /**
     * Assert that DNI follows Spanish format
     */
    protected function assertValidSpanishDNI(string $dni): void
    {
        $this->assertMatchesRegularExpression('/^\d{8}[A-Z]$/', $dni, "DNI should follow Spanish format: 8 digits + letter");
    }

    /**
     * Simulate concurrent request scenario
     */
    protected function simulateConcurrentRequests(callable $request, int $count = 3): array
    {
        $results = [];
        
        // In a real scenario, you'd use actual parallel execution
        // For testing purposes, we'll simulate by making sequential requests
        for ($i = 0; $i < $count; $i++) {
            $results[] = $request();
        }
        
        return $results;
    }

    /**
     * Create test data with international formats
     */
    protected function createInternationalUserData(): array
    {
        return [
            'spanish' => [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos@test.es',
                'phone' => '+34600123456',
                'dni' => '12345678Z',
                'address' => 'Calle Mayor 123, Madrid, España',
            ],
            'french' => [
                'name' => 'Marie Dubois',
                'email' => 'marie@test.fr',
                'phone' => '+33123456789',
                'dni' => 'FR123456789',
                'address' => 'Rue de la Paix 456, Paris, France',
            ],
            'german' => [
                'name' => 'Hans Müller',
                'email' => 'hans@test.de',
                'phone' => '+49123456789',
                'dni' => 'DE123456789',
                'address' => 'Hauptstraße 789, Berlin, Deutschland',
            ],
            'uk' => [
                'name' => 'John Smith',
                'email' => 'john@test.co.uk',
                'phone' => '+44123456789',
                'dni' => 'UK123456789',
                'address' => 'Main Street 321, London, UK',
            ],
        ];
    }

    // Additional helper methods for specific test scenarios

    /**
     * Create a valid user for testing (shorthand for createValidUserData)
     */
    protected function createValidUser(array $overrides = []): User
    {
        return User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+34600123456',
            'dni' => '12345678Z',
            'address' => 'Test Address',
            'password' => bcrypt('password123'),
        ], $overrides));
    }

    /**
     * Create CSV file with complex data (for FileProcessingAdvancedTest)
     */
    protected function createComplexCsvFile(): UploadedFile
    {
        $complexData = [
            [
                'name' => 'José María López-García',
                'email' => 'jose.maria@empresa.es',
                'phone' => '+34 600 12 34 56',
                'dni' => '12345678-Z',
                'address' => 'Calle Mayor, 123, 2º B, 28001 Madrid',
            ],
            [
                'name' => 'François Müller',
                'email' => 'francois@company.fr',
                'phone' => '+33.1.23.45.67.89',
                'dni' => 'FR12345678901',
                'address' => 'Rue de la Paix, 456, Paris 75001',
            ],
        ];

        return $this->createCSVFile($complexData, 'complex_users.csv');
    }

    /**
     * Create multi-sheet Excel file (simulated)
     */
    protected function createMultiSheetExcelFile(): UploadedFile
    {
        $data = [
            ['name' => 'Sheet1 User', 'email' => 'user1@test.com', 'phone' => '+34600123456', 'dni' => '12345678A', 'address' => 'Address 1'],
            ['name' => 'Sheet2 User', 'email' => 'user2@test.com', 'phone' => '+34600123457', 'dni' => '12345678B', 'address' => 'Address 2'],
        ];

        return $this->createExcelFile($data, 'multi_sheet.xlsx');
    }

    /**
     * Create CSV file with messy data
     */
    protected function createCsvFileWithMessyData(): UploadedFile
    {
        $messyData = [
            [
                'name' => '  José   María  ',
                'email' => 'JOSE@TEST.COM',
                'phone' => '600123456',
                'dni' => '12345678z',
                'address' => '   Calle Mayor   123   ',
            ],
            [
                'name' => 'maría-carmen',
                'email' => 'maria@Test.Com',
                'phone' => '(+34) 600-123-457',
                'dni' => '87654321B',
                'address' => 'Plaza Central 456',
            ],
        ];

        return $this->createCSVFile($messyData, 'messy_data.csv');
    }

    /**
     * Create CSV file with mixed valid/invalid data
     */
    protected function createCsvFileWithMixedData(): UploadedFile
    {
        $mixedData = [
            [
                'name' => 'Valid User',
                'email' => 'valid@test.com',
                'phone' => '+34600123456',
                'dni' => '12345678Z',
                'address' => 'Valid Address',
            ],
            [
                'name' => 'Invalid User',
                'email' => 'invalid-email',
                'phone' => 'invalid-phone',
                'dni' => 'invalid-dni',
                'address' => '',
            ],
        ];

        return $this->createCSVFile($mixedData, 'mixed_data.csv');
    }

    /**
     * Create CSV file with specific encoding
     */
    protected function createCsvFileWithEncoding(string $encoding = 'UTF-8'): UploadedFile
    {
        return $this->createEncodingCSVFile();
    }

    /**
     * Create CSV file with custom delimiter
     */
    protected function createCsvFileWithDelimiter(string $delimiter = ';'): UploadedFile
    {
        $data = [
            ['name' => 'Test User', 'email' => 'test@example.com', 'phone' => '+34600123456', 'dni' => '12345678Z', 'address' => 'Test Address']
        ];

        $csvContent = implode($delimiter, array_keys($data[0])) . "\n";
        foreach ($data as $row) {
            $csvContent .= implode($delimiter, array_map(function($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, $row)) . "\n";
        }

        $tempFile = tmpfile();
        fwrite($tempFile, $csvContent);
        $tempPath = stream_get_meta_data($tempFile)['uri'];

        return new UploadedFile($tempPath, 'delimiter_test.csv', 'text/csv', null, true);
    }

    /**
     * Create CSV file with different data types
     */
    protected function createCsvFileWithTypes(): UploadedFile
    {
        $data = [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+34600123456',
                'dni' => '12345678Z',
                'address' => 'Test Address',
                'age' => '25',
                'salary' => '30000.50',
                'active' => 'true',
            ]
        ];

        return $this->createCSVFile($data, 'types_test.csv');
    }

    /**
     * Create valid CSV file (generic helper)
     */
    protected function createValidCsvFile(int $userCount = 3): UploadedFile
    {
        $data = [];
        for ($i = 1; $i <= $userCount; $i++) {
            $data[] = [
                'name' => "Usuario CSV {$i}",
                'email' => "csv{$i}@test.com",
                'phone' => "+3460012345{$i}",
                'dni' => "7654321{$i}A",
                'address' => "Dirección CSV {$i}",
            ];
        }

        return $this->createCSVFile($data, 'valid_users.csv');
    }
}