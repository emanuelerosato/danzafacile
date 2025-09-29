<?php

namespace Tests\Unit\Models;

use App\Models\MediaGallery;
use App\Models\MediaItem;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaGalleryTestSimple extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create([
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

        $this->user = User::create([
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@user.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'school_id' => $this->school->id,
            'active' => true
        ]);
    }

    /** @test */
    public function it_can_create_a_gallery()
    {
        $gallery = MediaGallery::create([
            'title' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
            'is_public' => true
        ]);

        $this->assertDatabaseHas('media_galleries', [
            'title' => 'Test Gallery',
            'school_id' => $this->school->id
        ]);
    }

    /** @test */
    public function it_belongs_to_a_school()
    {
        $gallery = MediaGallery::create([
            'title' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
            'is_public' => true
        ]);

        $this->assertInstanceOf(School::class, $gallery->school);
        $this->assertEquals($this->school->id, $gallery->school->id);
    }

    /** @test */
    public function it_has_many_media_items()
    {
        $gallery = MediaGallery::create([
            'title' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
            'is_public' => true
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

        $this->assertCount(1, $gallery->mediaItems);
        $this->assertInstanceOf(MediaItem::class, $gallery->mediaItems->first());
    }

    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $gallery = new MediaGallery([
            'is_public' => '1',
            'is_featured' => '0'
        ]);

        $this->assertTrue($gallery->is_public);
        $this->assertFalse($gallery->is_featured);
        $this->assertIsBool($gallery->is_public);
        $this->assertIsBool($gallery->is_featured);
    }

    /** @test */
    public function it_handles_timestamps_correctly()
    {
        $gallery = MediaGallery::create([
            'title' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $this->school->id,
            'created_by' => $this->user->id,
            'is_public' => true
        ]);

        $this->assertNotNull($gallery->created_at);
        $this->assertNotNull($gallery->updated_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $gallery->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $gallery->updated_at);
    }
}