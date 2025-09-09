<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $user = User::factory()->create([
            'first_name' => 'Marco',
            'last_name' => 'Rossi',
            'email' => 'marco.rossi@example.com',
            'role' => 'user',
        ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Marco',
            'last_name' => 'Rossi',
            'email' => 'marco.rossi@example.com',
            'role' => 'user',
        ]);
    }

    public function test_user_belongs_to_school()
    {
        $school = School::factory()->create();
        $user = User::factory()->create(['school_id' => $school->id]);

        $this->assertEquals($school->id, $user->school->id);
        $this->assertEquals($school->name, $user->school->name);
    }

    public function test_user_full_name_accessor()
    {
        $user = User::factory()->create([
            'first_name' => 'Marco',
            'last_name' => 'Rossi',
        ]);

        $this->assertEquals('Marco Rossi', $user->full_name);
    }

    public function test_user_full_name_accessor_with_empty_names()
    {
        $user = User::factory()->create([
            'name' => 'Marco Rossi',
            'first_name' => '',
            'last_name' => '',
        ]);

        $this->assertEquals('Marco Rossi', $user->full_name);
    }

    public function test_user_phone_mutator()
    {
        $user = User::factory()->create([
            'phone' => '+39 123 456-789',
        ]);

        $this->assertEquals('+39123456789', $user->phone);
    }

    public function test_user_role_scopes()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->user()->create();

        // Test individual scopes
        $this->assertTrue(User::byRole('super_admin')->get()->contains($superAdmin));
        $this->assertTrue(User::byRole('admin')->get()->contains($admin));
        $this->assertTrue(User::byRole('user')->get()->contains($user));
    }

    public function test_user_active_scope()
    {
        $activeUser = User::factory()->create(['active' => true]);
        $inactiveUser = User::factory()->create(['active' => false]);

        $activeUsers = User::active()->get();

        $this->assertTrue($activeUsers->contains($activeUser));
        $this->assertFalse($activeUsers->contains($inactiveUser));
    }

    public function test_user_role_helper_methods()
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->admin()->create();
        $user = User::factory()->user()->create();

        // Test super admin
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($superAdmin->isAdmin());
        $this->assertFalse($superAdmin->isStudent());

        // Test admin
        $this->assertFalse($admin->isSuperAdmin());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isStudent());

        // Test user/student
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isStudent());
    }

    public function test_user_role_validation_mutator()
    {
        // Valid role
        $user = User::factory()->create(['role' => 'admin']);
        $this->assertEquals('admin', $user->role);

        // Invalid role should default to 'user'
        $user = new User();
        $user->role = 'invalid_role';
        $this->assertEquals('user', $user->role);
    }

    public function test_user_profile_image_url_accessor()
    {
        $user = User::factory()->create([
            'profile_image_path' => 'profiles/user-123.jpg',
        ]);

        $expectedUrl = asset('storage/profiles/user-123.jpg');
        $this->assertEquals($expectedUrl, $user->profile_image_url);

        // Test null case
        $userWithoutImage = User::factory()->create(['profile_image_path' => null]);
        $this->assertNull($userWithoutImage->profile_image_url);
    }
}