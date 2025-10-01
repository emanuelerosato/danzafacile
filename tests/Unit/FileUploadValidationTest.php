<?php

namespace Tests\Unit;

use App\Helpers\FileUploadHelper;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;

class FileUploadValidationTest extends TestCase
{
    /**
     * Test FileUploadHelper with valid JPEG image
     */
    public function test_valid_jpeg_image_passes_validation(): void
    {
        // Create a fake JPEG file
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $result = FileUploadHelper::validateFile($file, 'image', 5);

        $this->assertTrue($result['valid'], 'Valid JPEG should pass validation');
        $this->assertEmpty($result['errors'], 'Valid JPEG should have no errors');
    }

    /**
     * Test FileUploadHelper with valid PNG image
     */
    public function test_valid_png_image_passes_validation(): void
    {
        $file = UploadedFile::fake()->image('test.png', 100, 100);

        $result = FileUploadHelper::validateFile($file, 'image', 5);

        $this->assertTrue($result['valid'], 'Valid PNG should pass validation');
        $this->assertEmpty($result['errors'], 'Valid PNG should have no errors');
    }

    /**
     * Test FileUploadHelper with oversized file
     */
    public function test_oversized_file_fails_validation(): void
    {
        // Create a 10MB file (when max is 5MB)
        $file = UploadedFile::fake()->create('large.jpg', 10240); // 10MB

        $result = FileUploadHelper::validateFile($file, 'image', 5);

        $this->assertFalse($result['valid'], 'Oversized file should fail validation');
        $this->assertNotEmpty($result['errors'], 'Oversized file should have errors');
    }

    /**
     * Test sanitizeLikeInput escapes SQL wildcards
     */
    public function test_sanitize_like_input_escapes_wildcards(): void
    {
        $input = 'test%value_with\\backslash';
        $sanitized = \App\Helpers\QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals('test\\%value\\_with\\\\backslash', $sanitized);
    }

    /**
     * Test sanitizeFileName removes dangerous characters
     */
    public function test_sanitize_filename_removes_dangerous_characters(): void
    {
        $filename = '../../../etc/passwd';
        $sanitized = FileUploadHelper::sanitizeFileName($filename);

        $this->assertStringNotContainsString('..', $sanitized);
        $this->assertStringNotContainsString('/', $sanitized);
    }

    /**
     * Test getMimeTypeFromMagicBytes correctly identifies JPEG
     */
    public function test_get_mime_type_from_magic_bytes_identifies_jpeg(): void
    {
        $jpegHeader = "\xFF\xD8\xFF";
        $tmpFile = tmpfile();
        fwrite($tmpFile, $jpegHeader . str_repeat("\x00", 100));
        $path = stream_get_meta_data($tmpFile)['uri'];

        $mimeType = FileUploadHelper::getMimeTypeFromMagicBytes($path);

        $this->assertEquals('image/jpeg', $mimeType);

        fclose($tmpFile);
    }

    /**
     * Test getMimeTypeFromMagicBytes correctly identifies PNG
     */
    public function test_get_mime_type_from_magic_bytes_identifies_png(): void
    {
        $pngHeader = "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";
        $tmpFile = tmpfile();
        fwrite($tmpFile, $pngHeader . str_repeat("\x00", 100));
        $path = stream_get_meta_data($tmpFile)['uri'];

        $mimeType = FileUploadHelper::getMimeTypeFromMagicBytes($path);

        $this->assertEquals('image/png', $mimeType);

        fclose($tmpFile);
    }

    /**
     * Test getMimeTypeFromMagicBytes correctly identifies PDF
     */
    public function test_get_mime_type_from_magic_bytes_identifies_pdf(): void
    {
        $pdfHeader = "%PDF-1.4";
        $tmpFile = tmpfile();
        fwrite($tmpFile, $pdfHeader . str_repeat("\x00", 100));
        $path = stream_get_meta_data($tmpFile)['uri'];

        $mimeType = FileUploadHelper::getMimeTypeFromMagicBytes($path);

        $this->assertEquals('application/pdf', $mimeType);

        fclose($tmpFile);
    }
}
