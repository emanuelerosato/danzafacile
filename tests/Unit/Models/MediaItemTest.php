<?php

namespace Tests\Unit\Models;

use App\Models\MediaGallery;
use App\Models\MediaItem;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_media_gallery()
    {
        $school = School::create([
            'name' => 'Test School',
            'description' => 'Test description',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'TC',
            'postal_code' => '12345',
            'phone' => '1234567890',
            'email' => 'test@school.com',
            'active' => true
        ]);

        $gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => true
        ]);

        $mediaItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'test.jpg',
            'original_filename' => 'original.jpg',
            'file_path' => 'galleries/test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()
        ]);

        $this->assertInstanceOf(MediaGallery::class, $mediaItem->mediaGallery);
        $this->assertEquals($gallery->id, $mediaItem->mediaGallery->id);
    }

    /** @test */
    public function it_correctly_identifies_image_types()
    {
        $imageItem = new MediaItem([
            'mime_type' => 'image/jpeg',
            'type' => 'image'
        ]);

        $this->assertTrue($imageItem->isImage());
        $this->assertFalse($imageItem->isVideo());
    }

    /** @test */
    public function it_correctly_identifies_video_types()
    {
        $videoItem = new MediaItem([
            'mime_type' => 'video/mp4',
            'type' => 'video'
        ]);

        $this->assertTrue($videoItem->isVideo());
        $this->assertFalse($videoItem->isImage());
    }

    /** @test */
    public function it_generates_correct_storage_url()
    {
        $mediaItem = new MediaItem([
            'file_path' => 'galleries/test.jpg'
        ]);

        $expectedUrl = asset('storage/galleries/test.jpg');
        $this->assertEquals($expectedUrl, $mediaItem->getUrlAttribute());
    }

    /** @test */
    public function it_generates_correct_thumbnail_url_for_images()
    {
        $imageItem = new MediaItem([
            'file_path' => 'galleries/test.jpg',
            'type' => 'image'
        ]);

        $expectedUrl = asset('storage/galleries/thumbs/test.jpg');
        $this->assertEquals($expectedUrl, $imageItem->getThumbnailUrlAttribute());
    }

    /** @test */
    public function it_generates_default_thumbnail_for_videos()
    {
        $videoItem = new MediaItem([
            'file_path' => 'galleries/test.mp4',
            'type' => 'video'
        ]);

        $expectedUrl = asset('images/video-thumbnail.png');
        $this->assertEquals($expectedUrl, $videoItem->getThumbnailUrlAttribute());
    }

    /** @test */
    public function it_formats_file_size_correctly()
    {
        $smallFile = new MediaItem(['file_size' => 1024]); // 1KB
        $this->assertEquals('1.00 KB', $smallFile->getFormattedSizeAttribute());

        $mediumFile = new MediaItem(['file_size' => 1048576]); // 1MB
        $this->assertEquals('1.00 MB', $mediumFile->getFormattedSizeAttribute());

        $largeFile = new MediaItem(['file_size' => 1073741824]); // 1GB
        $this->assertEquals('1.00 GB', $largeFile->getFormattedSizeAttribute());

        $tinyFile = new MediaItem(['file_size' => 512]); // 512 bytes
        $this->assertEquals('512 B', $tinyFile->getFormattedSizeAttribute());
    }

    /** @test */
    public function it_scopes_images_correctly()
    {
        $school = School::create([
            'name' => 'Test School',
            'description' => 'Test description',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'TC',
            'postal_code' => '12345',
            'phone' => '1234567890',
            'email' => 'test@school.com',
            'active' => true
        ]);

        $gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => true
        ]);

        $imageItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'image.jpg',
            'original_filename' => 'image.jpg',
            'file_path' => 'galleries/image.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()
        ]);

        $videoItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'video.mp4',
            'original_filename' => 'video.mp4',
            'file_path' => 'galleries/video.mp4',
            'file_size' => 2048,
            'mime_type' => 'video/mp4',
            'type' => 'video',
            'uploaded_at' => now()
        ]);

        $images = MediaItem::images()->get();

        $this->assertCount(1, $images);
        $this->assertEquals($imageItem->id, $images->first()->id);
    }

    /** @test */
    public function it_scopes_videos_correctly()
    {
        $school = School::create([
            'name' => 'Test School',
            'description' => 'Test description',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'TC',
            'postal_code' => '12345',
            'phone' => '1234567890',
            'email' => 'test@school.com',
            'active' => true
        ]);

        $gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => true
        ]);

        $imageItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'image.jpg',
            'original_filename' => 'image.jpg',
            'file_path' => 'galleries/image.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()
        ]);

        $videoItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'video.mp4',
            'original_filename' => 'video.mp4',
            'file_path' => 'galleries/video.mp4',
            'file_size' => 2048,
            'mime_type' => 'video/mp4',
            'type' => 'video',
            'uploaded_at' => now()
        ]);

        $videos = MediaItem::videos()->get();

        $this->assertCount(1, $videos);
        $this->assertEquals($videoItem->id, $videos->first()->id);
    }

    /** @test */
    public function it_scopes_featured_items_correctly()
    {
        $school = School::create([
            'name' => 'Test School',
            'description' => 'Test description',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'TC',
            'postal_code' => '12345',
            'phone' => '1234567890',
            'email' => 'test@school.com',
            'active' => true
        ]);

        $gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => true
        ]);

        $featuredItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'featured.jpg',
            'original_filename' => 'featured.jpg',
            'file_path' => 'galleries/featured.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'is_featured' => true,
            'uploaded_at' => now()
        ]);

        $regularItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'regular.jpg',
            'original_filename' => 'regular.jpg',
            'file_path' => 'galleries/regular.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        $featuredItems = MediaItem::featured()->get();

        $this->assertCount(1, $featuredItems);
        $this->assertEquals($featuredItem->id, $featuredItems->first()->id);
    }

    /** @test */
    public function it_handles_uploaded_at_carbon_casting()
    {
        $school = School::create([
            'name' => 'Test School',
            'description' => 'Test description',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'TC',
            'postal_code' => '12345',
            'phone' => '1234567890',
            'email' => 'test@school.com',
            'active' => true
        ]);

        $gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => true
        ]);

        $mediaItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'test.jpg',
            'original_filename' => 'original.jpg',
            'file_path' => 'galleries/test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => '2023-12-01 10:00:00'
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $mediaItem->uploaded_at);
        $this->assertEquals('2023-12-01 10:00:00', $mediaItem->uploaded_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $mediaItem = new MediaItem([
            'is_featured' => '1'
        ]);

        $this->assertTrue($mediaItem->is_featured);
        $this->assertIsBool($mediaItem->is_featured);

        $mediaItem = new MediaItem([
            'is_featured' => '0'
        ]);

        $this->assertFalse($mediaItem->is_featured);
        $this->assertIsBool($mediaItem->is_featured);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $mediaItem = new MediaItem();

        // These would be handled by form request validation in the controller
        $requiredFields = [
            'media_gallery_id',
            'filename',
            'original_filename',
            'file_path',
            'file_size',
            'mime_type',
            'type'
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $mediaItem->getFillable());
        }
    }

    /** @test */
    public function it_determines_type_from_mime_type()
    {
        // Test image mime types
        $jpegItem = new MediaItem(['mime_type' => 'image/jpeg']);
        $this->assertTrue($jpegItem->isImage());

        $pngItem = new MediaItem(['mime_type' => 'image/png']);
        $this->assertTrue($pngItem->isImage());

        $gifItem = new MediaItem(['mime_type' => 'image/gif']);
        $this->assertTrue($gifItem->isImage());

        // Test video mime types
        $mp4Item = new MediaItem(['mime_type' => 'video/mp4']);
        $this->assertTrue($mp4Item->isVideo());

        $movItem = new MediaItem(['mime_type' => 'video/quicktime']);
        $this->assertTrue($movItem->isVideo());

        $aviItem = new MediaItem(['mime_type' => 'video/x-msvideo']);
        $this->assertTrue($aviItem->isVideo());
    }

    /** @test */
    public function it_handles_file_extension_extraction()
    {
        $mediaItem = new MediaItem([
            'filename' => 'test.jpg'
        ]);

        $this->assertEquals('jpg', $mediaItem->getFileExtensionAttribute());

        $mediaItem = new MediaItem([
            'filename' => 'video.mp4'
        ]);

        $this->assertEquals('mp4', $mediaItem->getFileExtensionAttribute());

        $mediaItem = new MediaItem([
            'filename' => 'file.with.multiple.dots.png'
        ]);

        $this->assertEquals('png', $mediaItem->getFileExtensionAttribute());
    }

    /** @test */
    public function it_orders_by_upload_date_descending_by_default()
    {
        $school = School::create([
            'name' => 'Test School',
            'description' => 'Test description',
            'address' => 'Test Address',
            'city' => 'Test City',
            'province' => 'TC',
            'postal_code' => '12345',
            'phone' => '1234567890',
            'email' => 'test@school.com',
            'active' => true
        ]);

        $gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => true
        ]);

        $oldItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'old.jpg',
            'original_filename' => 'old.jpg',
            'file_path' => 'galleries/old.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()->subHours(2)
        ]);

        $newItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'new.jpg',
            'original_filename' => 'new.jpg',
            'file_path' => 'galleries/new.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()
        ]);

        $items = MediaItem::latest('uploaded_at')->get();

        $this->assertEquals($newItem->id, $items->first()->id);
        $this->assertEquals($oldItem->id, $items->last()->id);
    }

    /** @test */
    public function it_provides_human_readable_upload_time()
    {
        $mediaItem = new MediaItem([
            'uploaded_at' => now()->subMinutes(30)
        ]);

        $this->assertStringContains('30 minutes ago', $mediaItem->uploaded_at->diffForHumans());

        $mediaItem = new MediaItem([
            'uploaded_at' => now()->subDays(2)
        ]);

        $this->assertStringContains('2 days ago', $mediaItem->uploaded_at->diffForHumans());
    }
}