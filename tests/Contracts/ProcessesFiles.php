<?php

namespace Tests\Contracts;

use Illuminate\Http\UploadedFile;

/**
 * Interface for classes that process file uploads
 * Follows Interface Segregation Principle
 */
interface ProcessesFiles
{
    /**
     * Process uploaded file and extract user data
     */
    public function processUploadedFile(UploadedFile $file): array;

    /**
     * Validate file format and size
     */
    public function validateFileFormat(UploadedFile $file): bool;

    /**
     * Generate CSV template for download
     */
    public function generateCsvTemplate(): string;
}