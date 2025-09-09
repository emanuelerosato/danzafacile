<?php

namespace Tests\Unit\Models;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_can_be_created()
    {
        $school = School::factory()->create([
            'name' => 'Test Dance School',
            'email' => 'test@school.com',
        ]);

        $this->assertDatabaseHas('schools', [
            'name' => 'Test Dance School',
            'email' => 'test@school.com',
        ]);
    }

    public function test_school_has_many_users()
    {
        $school = School::factory()->create();
        $user1 = User::factory()->create(['school_id' => $school->id]);
        $user2 = User::factory()->create(['school_id' => $school->id]);

        $this->assertCount(2, $school->users);
        $this->assertTrue($school->users->contains($user1));
        $this->assertTrue($school->users->contains($user2));
    }

    public function test_school_has_many_courses()
    {
        $school = School::factory()->create();
        
        // We'll test this once Course factory is created
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $school->courses);
    }

    public function test_school_active_scope()
    {
        $activeSchool = School::factory()->create(['active' => true]);
        $inactiveSchool = School::factory()->create(['active' => false]);

        $activeSchools = School::active()->get();

        $this->assertTrue($activeSchools->contains($activeSchool));
        $this->assertFalse($activeSchools->contains($inactiveSchool));
    }

    public function test_school_phone_mutator()
    {
        $school = School::create([
            'name' => 'Test School',
            'email' => 'test@school.com',
            'phone' => '+39 123 456 789',
            'address' => 'Test Address',
        ]);

        // Phone should be cleaned (only numbers and +)
        $this->assertEquals('+39123456789', $school->phone);
    }

    public function test_school_logo_url_accessor()
    {
        $school = School::factory()->create([
            'logo_path' => 'logos/test-school.png',
        ]);

        $expectedUrl = asset('storage/logos/test-school.png');
        $this->assertEquals($expectedUrl, $school->logo_url);
    }

    public function test_school_helper_methods()
    {
        $school = School::factory()->create();

        // Test helper methods return numbers (actual counts will depend on data)
        $this->assertIsInt($school->getTotalStudentsCount());
        $this->assertIsInt($school->getActiveCoursesCount());
        $this->assertIsFloat($school->getTotalRevenue());
    }
}