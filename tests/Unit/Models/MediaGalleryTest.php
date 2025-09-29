<?php

namespace Tests\Unit\Models;

use App\Models\MediaGallery;
use App\Models\MediaItem;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaGalleryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_a_school()
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

        $this->assertInstanceOf(School::class, $gallery->school);
        $this->assertEquals($school->id, $gallery->school->id);
    }

    /** @test */
    public function it_has_many_media_items()
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

        $mediaItem1 = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'test1.jpg',
            'original_filename' => 'original1.jpg',
            'file_path' => 'galleries/test1.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()
        ]);

        $mediaItem2 = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'test2.jpg',
            'original_filename' => 'original2.jpg',
            'file_path' => 'galleries/test2.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()
        ]);

        $this->assertCount(2, $gallery->mediaItems);
        $this->assertInstanceOf(MediaItem::class, $gallery->mediaItems->first());
    }

    /** @test */
    public function it_can_check_if_public()
    {
        $gallery = new MediaGallery(['is_public' => true]);
        $this->assertTrue($gallery->isPublic());

        $gallery = new MediaGallery(['is_public' => false]);
        $this->assertFalse($gallery->isPublic());
    }

    /** @test */
    public function it_can_check_if_active()
    {
        $gallery = new MediaGallery(['active' => true]);
        $this->assertTrue($gallery->isActive());

        $gallery = new MediaGallery(['active' => false]);
        $this->assertFalse($gallery->isActive());
    }

    /** @test */
    public function it_scopes_public_galleries()
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

        $publicGallery = MediaGallery::create([
            'name' => 'Public Gallery',
            'description' => 'Public gallery',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => true
        ]);

        $privateGallery = MediaGallery::create([
            'name' => 'Private Gallery',
            'description' => 'Private gallery',
            'school_id' => $school->id,
            'is_public' => false,
            'active' => true
        ]);

        $publicGalleries = MediaGallery::public()->get();

        $this->assertCount(1, $publicGalleries);
        $this->assertEquals($publicGallery->id, $publicGalleries->first()->id);
    }

    /** @test */
    public function it_scopes_active_galleries()
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

        $activeGallery = MediaGallery::create([
            'name' => 'Active Gallery',
            'description' => 'Active gallery',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => true
        ]);

        $inactiveGallery = MediaGallery::create([
            'name' => 'Inactive Gallery',
            'description' => 'Inactive gallery',
            'school_id' => $school->id,
            'is_public' => true,
            'active' => false
        ]);

        $activeGalleries = MediaGallery::active()->get();

        $this->assertCount(1, $activeGalleries);
        $this->assertEquals($activeGallery->id, $activeGalleries->first()->id);
    }

    /** @test */
    public function it_gets_media_count()
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

        // Gallery with no media items should return 0
        $this->assertEquals(0, $gallery->getMediaCountAttribute());

        // Add media items
        MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'test1.jpg',
            'original_filename' => 'original1.jpg',
            'file_path' => 'galleries/test1.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()
        ]);

        MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'test2.jpg',
            'original_filename' => 'original2.jpg',
            'file_path' => 'galleries/test2.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'uploaded_at' => now()
        ]);

        // Refresh gallery to get updated media count
        $gallery = $gallery->fresh();
        $this->assertEquals(2, $gallery->getMediaCountAttribute());
    }

    /** @test */
    public function it_gets_featured_media_item()
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

        $regularItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'regular.jpg',
            'original_filename' => 'regular.jpg',
            'file_path' => 'galleries/regular.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Regular Item',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        $featuredItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'featured.jpg',
            'original_filename' => 'featured.jpg',
            'file_path' => 'galleries/featured.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Featured Item',
            'is_featured' => true,
            'uploaded_at' => now()
        ]);

        $this->assertInstanceOf(MediaItem::class, $gallery->getFeaturedMediaAttribute());
        $this->assertEquals($featuredItem->id, $gallery->getFeaturedMediaAttribute()->id);
    }

    /** @test */
    public function it_handles_no_featured_media_gracefully()
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

        // Gallery with no featured items should return null
        $this->assertNull($gallery->getFeaturedMediaAttribute());

        // Gallery with media items but none featured should return first item
        $firstItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'first.jpg',
            'original_filename' => 'first.jpg',
            'file_path' => 'galleries/first.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'filename' => 'second.jpg',
            'original_filename' => 'second.jpg',
            'file_path' => 'galleries/second.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        $this->assertInstanceOf(MediaItem::class, $gallery->getFeaturedMediaAttribute());
        $this->assertEquals($firstItem->id, $gallery->getFeaturedMediaAttribute()->id);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $gallery = new MediaGallery();

        $this->assertFalse($gallery->isValid());
        $this->assertArrayHasKey('name', $gallery->getErrors());
        $this->assertArrayHasKey('school_id', $gallery->getErrors());
    }

    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $gallery = new MediaGallery([
            'is_public' => '1',
            'active' => '0'
        ]);

        $this->assertTrue($gallery->is_public);
        $this->assertFalse($gallery->active);
        $this->assertIsBool($gallery->is_public);
        $this->assertIsBool($gallery->active);
    }

    /** @test */
    public function it_handles_timestamps_correctly()
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

        $this->assertNotNull($gallery->created_at);
        $this->assertNotNull($gallery->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $gallery->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $gallery->updated_at);
    }
}