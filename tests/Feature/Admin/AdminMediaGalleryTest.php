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

class AdminMediaGalleryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $school;
    protected $admin;
    protected $otherSchool;
    protected $otherAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test school
        $this->school = School::create([
            'name' => 'Test Dance School',
            'description' => 'A test school for MediaGallery testing',
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

        // Create another school for multi-tenancy tests
        $this->otherSchool = School::create([
            'name' => 'Other Dance School',
            'description' => 'Another test school',
            'address' => 'Other Street 456',
            'city' => 'Other City',
            'province' => 'OC',
            'postal_code' => '67890',
            'phone' => '0987654321',
            'email' => 'other@danceschool.com',
            'active' => true
        ]);

        // Create other admin
        $this->otherAdmin = User::create([
            'name' => 'Other Admin',
            'first_name' => 'Other',
            'last_name' => 'Admin',
            'email' => 'other@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'school_id' => $this->otherSchool->id,
            'active' => true,
            'email_verified_at' => now()
        ]);

        // Set up storage for testing
        Storage::fake('public');
    }

    /** @test */
    public function admin_can_view_media_galleries_index()
    {
        // Create test galleries
        $gallery1 = MediaGallery::create([
            'name' => 'Test Gallery 1',
            'description' => 'First test gallery',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        $gallery2 = MediaGallery::create([
            'name' => 'Test Gallery 2',
            'description' => 'Second test gallery',
            'school_id' => $this->school->id,
            'is_public' => false,
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.index'));

        $response->assertStatus(200);
        $response->assertSee('Test Gallery 1');
        $response->assertSee('Test Gallery 2');
    }

    /** @test */
    public function admin_cannot_see_other_schools_galleries()
    {
        // Create gallery for other school
        $otherGallery = MediaGallery::create([
            'name' => 'Other School Gallery',
            'description' => 'Gallery from other school',
            'school_id' => $this->otherSchool->id,
            'is_public' => true,
            'active' => true
        ]);

        // Create gallery for current school
        $myGallery = MediaGallery::create([
            'name' => 'My School Gallery',
            'description' => 'Gallery from my school',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.index'));

        $response->assertStatus(200);
        $response->assertSee('My School Gallery');
        $response->assertDontSee('Other School Gallery');
    }

    /** @test */
    public function admin_can_create_gallery()
    {
        $galleryData = [
            'name' => 'New Test Gallery',
            'description' => 'A newly created gallery',
            'is_public' => true,
            'active' => true
        ];

        $response = $this->actingAs($this->admin)
                        ->post(route('admin.media-galleries.store'), $galleryData);

        $response->assertRedirect();
        $this->assertDatabaseHas('media_galleries', [
            'name' => 'New Test Gallery',
            'school_id' => $this->school->id
        ]);
    }

    /** @test */
    public function admin_can_view_gallery_details()
    {
        $gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test gallery description',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.show', $gallery));

        $response->assertStatus(200);
        $response->assertSee('Test Gallery');
        $response->assertSee('Test gallery description');
    }

    /** @test */
    public function admin_cannot_view_other_schools_gallery()
    {
        $otherGallery = MediaGallery::create([
            'name' => 'Other School Gallery',
            'description' => 'Gallery from other school',
            'school_id' => $this->otherSchool->id,
            'is_public' => true,
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.show', $otherGallery));

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_update_gallery()
    {
        $gallery = MediaGallery::create([
            'name' => 'Original Gallery',
            'description' => 'Original description',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        $updateData = [
            'name' => 'Updated Gallery',
            'description' => 'Updated description',
            'is_public' => false,
            'active' => true
        ];

        $response = $this->actingAs($this->admin)
                        ->put(route('admin.media-galleries.update', $gallery), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('media_galleries', [
            'id' => $gallery->id,
            'name' => 'Updated Gallery',
            'description' => 'Updated description',
            'is_public' => false
        ]);
    }

    /** @test */
    public function admin_cannot_update_other_schools_gallery()
    {
        $otherGallery = MediaGallery::create([
            'name' => 'Other School Gallery',
            'description' => 'Gallery from other school',
            'school_id' => $this->otherSchool->id,
            'is_public' => true,
            'active' => true
        ]);

        $updateData = [
            'name' => 'Hacked Gallery',
            'description' => 'This should not work'
        ];

        $response = $this->actingAs($this->admin)
                        ->put(route('admin.media-galleries.update', $otherGallery), $updateData);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('media_galleries', [
            'id' => $otherGallery->id,
            'name' => 'Hacked Gallery'
        ]);
    }

    /** @test */
    public function admin_can_delete_gallery()
    {
        $gallery = MediaGallery::create([
            'name' => 'Gallery to Delete',
            'description' => 'This gallery will be deleted',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
                        ->delete(route('admin.media-galleries.destroy', $gallery));

        $response->assertRedirect();
        $this->assertDatabaseMissing('media_galleries', [
            'id' => $gallery->id
        ]);
    }

    /** @test */
    public function admin_cannot_delete_other_schools_gallery()
    {
        $otherGallery = MediaGallery::create([
            'name' => 'Other School Gallery',
            'description' => 'Gallery from other school',
            'school_id' => $this->otherSchool->id,
            'is_public' => true,
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
                        ->delete(route('admin.media-galleries.destroy', $otherGallery));

        $response->assertStatus(404);
        $this->assertDatabaseHas('media_galleries', [
            'id' => $otherGallery->id
        ]);
    }

    /** @test */
    public function gallery_creation_requires_valid_data()
    {
        $response = $this->actingAs($this->admin)
                        ->post(route('admin.media-galleries.store'), []);

        $response->assertSessionHasErrors(['name']);

        $response = $this->actingAs($this->admin)
                        ->post(route('admin.media-galleries.store'), [
                            'name' => '', // Empty name
                            'description' => 'Test description'
                        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function admin_can_toggle_gallery_status()
    {
        $gallery = MediaGallery::create([
            'name' => 'Test Gallery',
            'description' => 'Test description',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        // Test deactivation
        $response = $this->actingAs($this->admin)
                        ->patch(route('admin.media-galleries.toggle-status', $gallery));

        $response->assertRedirect();
        $this->assertDatabaseHas('media_galleries', [
            'id' => $gallery->id,
            'active' => false
        ]);

        // Test reactivation
        $response = $this->actingAs($this->admin)
                        ->patch(route('admin.media-galleries.toggle-status', $gallery));

        $response->assertRedirect();
        $this->assertDatabaseHas('media_galleries', [
            'id' => $gallery->id,
            'active' => true
        ]);
    }

    /** @test */
    public function admin_can_search_galleries()
    {
        MediaGallery::create([
            'name' => 'Summer Dance Photos',
            'description' => 'Photos from summer performances',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        MediaGallery::create([
            'name' => 'Winter Recital Videos',
            'description' => 'Videos from winter recital',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.index', ['search' => 'Summer']));

        $response->assertStatus(200);
        $response->assertSee('Summer Dance Photos');
        $response->assertDontSee('Winter Recital Videos');
    }

    /** @test */
    public function admin_can_filter_galleries_by_status()
    {
        MediaGallery::create([
            'name' => 'Active Gallery',
            'description' => 'Active gallery',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => true
        ]);

        MediaGallery::create([
            'name' => 'Inactive Gallery',
            'description' => 'Inactive gallery',
            'school_id' => $this->school->id,
            'is_public' => true,
            'active' => false
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('admin.media-galleries.index', ['status' => 'active']));

        $response->assertStatus(200);
        $response->assertSee('Active Gallery');
        $response->assertDontSee('Inactive Gallery');
    }

    /** @test */
    public function unauthenticated_users_cannot_access_admin_galleries()
    {
        $response = $this->get(route('admin.media-galleries.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function non_admin_users_cannot_access_admin_galleries()
    {
        $student = User::create([
            'name' => 'Test Student',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'school_id' => $this->school->id,
            'active' => true
        ]);

        $response = $this->actingAs($student)
                        ->get(route('admin.media-galleries.index'));

        $response->assertStatus(403);
    }
}