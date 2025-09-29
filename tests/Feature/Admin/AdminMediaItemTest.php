<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\School;
use App\Models\MediaGallery;
use App\Models\MediaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminMediaItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $school;
    protected $admin;
    protected $gallery;
    protected $otherSchool;
    protected $otherGallery;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test school
        $this->school = School::create([
            'name' => 'Test Dance School',
            'description' => 'A test school for MediaItem testing',
            'address' => 'Test Street 123',
            'city' => 'Test City',
            'province' => 'TC',
            'postal_code' => '12345',
            'phone' => '1234567890',
            'email' => 'test@danceschool.com',
            'active' => true
        ]);

        // Create admin user
        $this->admin = User::create([
            'name' => 'Test Admin',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'school_id' => $this->school->id,
            'active' => true,
            'email_verified_at' => now()
        ]);

        // Create test gallery
        $this->gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test gallery for media items',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        // Create other school and gallery for multi-tenancy tests
        $this->otherSchool = School::create([
            'name' => 'Other School',
            'description' => 'Other school',
            'address' => 'Other Street 456',
            'city' => 'Other City',
            'province' => 'OC',
            'postal_code' => '67890',
            'phone' => '0987654321',
            'email' => 'other@school.com',
            'active' => true
        ]);

        $this->otherGallery = MediaGallery::create([
            'name' => 'Other Gallery',
            'description' => 'Gallery from other school',
            'school_id' => $this->otherSchool->id,
            'is_public' => true,
            'active' => true
        ]);

        // Set up storage for testing
        Storage::fake('public');
    }

    /** @test */
    public function admin_can_view_media_items_in_gallery()
    {
        // Create test media items
        $item1 = MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'test1.jpg',
            'original_filename' => 'original1.jpg',
            'file_path' => 'galleries/test1.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Test Image 1',
            'description' => 'First test image',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        $item2 = MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'test2.mp4',
            'original_filename' => 'original2.mp4',
            'file_path' => 'galleries/test2.mp4',
            'file_size' => 2048,
            'mime_type' => 'video/mp4',
            'type' => 'video',
            'title' => 'Test Video 1',
            'description' => 'First test video',
            'is_featured' => true,
            'uploaded_at' => now()
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.media.index', $this->gallery));

        $response->assertStatus(200);
        $response->assertSee('Test Image 1');
        $response->assertSee('Test Video 1');
    }

    /** @test */
    public function admin_can_upload_image_to_gallery()
    {
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(500);

        $response = $this->actingAs($this->admin)
                        ->post(route('admin.media-galleries.media.store', $this->gallery), [
                            'files' => [$file],
                            'title' => 'Uploaded Test Image',
                            'description' => 'Test image description',
                            'is_featured' => false
                        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('media_items', [
            'media_gallery_id' => $this->gallery->id,
            'original_filename' => 'test.jpg',
            'type' => 'image',
            'mime_type' => 'image/jpeg'
        ]);
    }

    /** @test */
    public function admin_can_upload_video_to_gallery()
    {
        $file = UploadedFile::fake()->create('test.mp4', 1000, 'video/mp4');

        $response = $this->actingAs($this->admin)
                        ->post(route('admin.media-galleries.media.store', $this->gallery), [
                            'files' => [$file],
                            'title' => 'Uploaded Test Video',
                            'description' => 'Test video description',
                            'is_featured' => true
                        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('media_items', [
            'media_gallery_id' => $this->gallery->id,
            'original_filename' => 'test.mp4',
            'type' => 'video',
            'mime_type' => 'video/mp4'
        ]);
    }

    /** @test */
    public function admin_cannot_upload_to_other_schools_gallery()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($this->admin)
                        ->post(route('admin.media-galleries.media.store', $this->otherGallery), [
                            'files' => [$file],
                            'title' => 'Hacked Upload'
                        ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('media_items', [
            'media_gallery_id' => $this->otherGallery->id,
            'title' => 'Hacked Upload'
        ]);
    }

    /** @test */
    public function admin_can_update_media_item()
    {
        $mediaItem = MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'test.jpg',
            'original_filename' => 'original.jpg',
            'file_path' => 'galleries/test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Original Title',
            'description' => 'Original description',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'is_featured' => true
        ];

        $response = $this->actingAs($this->admin)
                        ->put(route('admin.media-galleries.media.update', [$this->gallery, $mediaItem]), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('media_items', [
            'id' => $mediaItem->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'is_featured' => true
        ]);
    }

    /** @test */
    public function admin_cannot_update_media_item_from_other_school()
    {
        $otherMediaItem = MediaItem::create([
            'media_gallery_id' => $this->otherGallery->id,
            'filename' => 'other.jpg',
            'original_filename' => 'other_original.jpg',
            'file_path' => 'galleries/other.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Other Title',
            'description' => 'Other description',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        $updateData = [
            'title' => 'Hacked Title',
            'description' => 'This should not work'
        ];

        $response = $this->actingAs($this->admin)
                        ->put(route('admin.media-galleries.media.update', [$this->otherGallery, $otherMediaItem]), $updateData);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('media_items', [
            'id' => $otherMediaItem->id,
            'title' => 'Hacked Title'
        ]);
    }

    /** @test */
    public function admin_can_delete_media_item()
    {
        $mediaItem = MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'test.jpg',
            'original_filename' => 'original.jpg',
            'file_path' => 'galleries/test.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Item to Delete',
            'description' => 'This will be deleted',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        $response = $this->actingAs($this->admin)
                        ->delete(route('admin.media-galleries.media.destroy', [$this->gallery, $mediaItem]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('media_items', [
            'id' => $mediaItem->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_media_item_from_other_school()
    {
        $otherMediaItem = MediaItem::create([
            'media_gallery_id' => $this->otherGallery->id,
            'filename' => 'other.jpg',
            'original_filename' => 'other_original.jpg',
            'file_path' => 'galleries/other.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Protected Item',
            'description' => 'Should not be deleted',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        $response = $this->actingAs($this->admin)
                        ->delete(route('admin.media-galleries.media.destroy', [$this->otherGallery, $otherMediaItem]));

        $response->assertStatus(404);
        $this->assertDatabaseHas('media_items', [
            'id' => $otherMediaItem->id
        ]);
    }

    /** @test */
    public function upload_validates_file_types()
    {
        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('test.txt', 100, 'text/plain');

        $response = $this->actingAs($this->admin)
                        ->post(route('admin.media-galleries.media.store', $this->gallery), [
                            'files' => [$invalidFile]
                        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('media_items', [
            'media_gallery_id' => $this->gallery->id,
            'original_filename' => 'test.txt'
        ]);
    }

    /** @test */
    public function upload_validates_file_size()
    {
        // Test oversized file (assuming max size is 10MB)
        $oversizedFile = UploadedFile::fake()->create('huge.jpg', 15000); // 15MB

        $response = $this->actingAs($this->admin)
                        ->post(route('admin.media-galleries.media.store', $this->gallery), [
                            'files' => [$oversizedFile]
                        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('media_items', [
            'media_gallery_id' => $this->gallery->id,
            'original_filename' => 'huge.jpg'
        ]);
    }

    /** @test */
    public function admin_can_set_featured_media_item()
    {
        $mediaItem1 = MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'test1.jpg',
            'original_filename' => 'original1.jpg',
            'file_path' => 'galleries/test1.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Item 1',
            'is_featured' => true,
            'uploaded_at' => now()
        ]);

        $mediaItem2 = MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'test2.jpg',
            'original_filename' => 'original2.jpg',
            'file_path' => 'galleries/test2.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Item 2',
            'is_featured' => false,
            'uploaded_at' => now()
        ]);

        // Set item2 as featured
        $response = $this->actingAs($this->admin)
                        ->patch(route('admin.media-galleries.media.toggle-featured', [$this->gallery, $mediaItem2]));

        $response->assertRedirect();

        // Check that item2 is now featured and item1 is not
        $this->assertDatabaseHas('media_items', [
            'id' => $mediaItem2->id,
            'is_featured' => true
        ]);

        $this->assertDatabaseHas('media_items', [
            'id' => $mediaItem1->id,
            'is_featured' => false
        ]);
    }

    /** @test */
    public function admin_can_bulk_delete_media_items()
    {
        $item1 = MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'test1.jpg',
            'original_filename' => 'original1.jpg',
            'file_path' => 'galleries/test1.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Item 1',
            'uploaded_at' => now()
        ]);

        $item2 = MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'test2.jpg',
            'original_filename' => 'original2.jpg',
            'file_path' => 'galleries/test2.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Item 2',
            'uploaded_at' => now()
        ]);

        $response = $this->actingAs($this->admin)
                        ->delete(route('admin.media-galleries.media.bulk-destroy', $this->gallery), [
                            'media_item_ids' => [$item1->id, $item2->id]
                        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('media_items', ['id' => $item1->id]);
        $this->assertDatabaseMissing('media_items', ['id' => $item2->id]);
    }

    /** @test */
    public function admin_can_search_media_items()
    {
        MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'summer.jpg',
            'original_filename' => 'summer_dance.jpg',
            'file_path' => 'galleries/summer.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Summer Performance',
            'description' => 'Great summer show',
            'uploaded_at' => now()
        ]);

        MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'winter.jpg',
            'original_filename' => 'winter_recital.jpg',
            'file_path' => 'galleries/winter.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Winter Recital',
            'description' => 'Amazing winter performance',
            'uploaded_at' => now()
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.media.index', [
                            'gallery' => $this->gallery,
                            'search' => 'Summer'
                        ]));

        $response->assertStatus(200);
        $response->assertSee('Summer Performance');
        $response->assertDontSee('Winter Recital');
    }

    /** @test */
    public function admin_can_filter_media_items_by_type()
    {
        MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'photo.jpg',
            'original_filename' => 'photo.jpg',
            'file_path' => 'galleries/photo.jpg',
            'file_size' => 1024,
            'mime_type' => 'image/jpeg',
            'type' => 'image',
            'title' => 'Photo Item',
            'uploaded_at' => now()
        ]);

        MediaItem::create([
            'media_gallery_id' => $this->gallery->id,
            'filename' => 'video.mp4',
            'original_filename' => 'video.mp4',
            'file_path' => 'galleries/video.mp4',
            'file_size' => 2048,
            'mime_type' => 'video/mp4',
            'type' => 'video',
            'title' => 'Video Item',
            'uploaded_at' => now()
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.media.index', [
                            'gallery' => $this->gallery,
                            'type' => 'image'
                        ]));

        $response->assertStatus(200);
        $response->assertSee('Photo Item');
        $response->assertDontSee('Video Item');
    }
}