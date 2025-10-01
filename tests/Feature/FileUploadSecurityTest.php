<?php

namespace Tests\Feature;

use App\Helpers\FileUploadHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FileUploadSecurityTest extends TestCase
{
    /**
     * Test FileUploadHelper validates JPEG files correctly
     */
    public function test_file_upload_helper_validates_jpeg_files(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $result = FileUploadHelper::validateFile($file, 'image', 5);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test FileUploadHelper validates PNG files correctly
     */
    public function test_file_upload_helper_validates_png_files(): void
    {
        $file = UploadedFile::fake()->image('test.png', 100, 100);

        $result = FileUploadHelper::validateFile($file, 'image', 5);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /**
     * Test FileUploadHelper rejects oversized files
     */
    public function test_file_upload_helper_rejects_oversized_files(): void
    {
        // Create a 10MB file (max is 5MB)
        $file = UploadedFile::fake()->create('large.jpg', 10240);

        $result = FileUploadHelper::validateFile($file, 'image', 5);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('dimensione', strtolower(implode(' ', $result['errors'])));
    }

    /**
     * Test sanitizeFileName removes path traversal characters
     */
    public function test_sanitize_filename_prevents_path_traversal(): void
    {
        $dangerousFilename = '../../../etc/passwd';
        $sanitized = FileUploadHelper::sanitizeFileName($dangerousFilename);

        $this->assertStringNotContainsString('..', $sanitized);
        $this->assertStringNotContainsString('/', $sanitized);
        $this->assertStringNotContainsString('\\', $sanitized);
    }

    /**
     * Test QueryHelper sanitizes LIKE input
     */
    public function test_query_helper_sanitizes_like_input(): void
    {
        $input = 'test%value_with\\backslash';
        $sanitized = \App\Helpers\QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals('test\\%value\\_with\\\\backslash', $sanitized);
        $this->assertStringContainsString('\\%', $sanitized);
        $this->assertStringContainsString('\\_', $sanitized);
    }

    /**
     * Test QueryHelper escapes percent wildcards
     */
    public function test_query_helper_escapes_percent_wildcards(): void
    {
        $input = '%admin%';
        $sanitized = \App\Helpers\QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals('\\%admin\\%', $sanitized);
    }

    /**
     * Test QueryHelper escapes underscore wildcards
     */
    public function test_query_helper_escapes_underscore_wildcards(): void
    {
        $input = 'test_user';
        $sanitized = \App\Helpers\QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals('test\\_user', $sanitized);
    }

    /**
     * Test QueryHelper escapes backslashes
     */
    public function test_query_helper_escapes_backslashes(): void
    {
        $input = 'path\\to\\file';
        $sanitized = \App\Helpers\QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals('path\\\\to\\\\file', $sanitized);
    }

    /**
     * Test FileUploadHelper rejects invalid mime types
     */
    public function test_file_upload_helper_validates_mime_types(): void
    {
        // Create a document file when expecting an image
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $result = FileUploadHelper::validateFile($file, 'image', 5);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }
}
