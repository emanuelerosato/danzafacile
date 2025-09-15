<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiStudentTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $student;
    protected $admin;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test school
        $this->school = School::create([
            'name' => 'Test Dance School',
            'description' => 'A test school for API testing',
            'address' => 'Test Street 123',
            'city' => 'Test City',
            'postal_code' => '12345',
            'phone' => '+1234567890',
            'email' => 'test@school.com',
            'active' => true,
        ]);

        // Create test users
        $this->student = User::create([
            'name' => 'Test Student',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'school_id' => $this->school->id,
            'active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Test Admin',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'school_id' => $this->school->id,
            'active' => true,
        ]);

        $this->token = $this->student->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function it_can_get_dashboard_quick_stats()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/mobile/v1/dashboard-quick');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'role',
                        ],
                        'school' => [
                            'id',
                            'name',
                        ],
                        'quick_stats' => [
                            'active_enrollments',
                            'pending_payments',
                            'total_courses',
                        ],
                    ],
                ]);
    }

    /** @test */
    public function it_can_browse_available_courses()
    {
        // Create test courses
        Course::create([
            'name' => 'Ballet Basics',
            'description' => 'Learn the fundamentals of ballet',
            'instructor_id' => $this->admin->id,
            'school_id' => $this->school->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'schedule' => 'Monday, Wednesday 18:00-19:30',
            'price' => 120.00,
            'max_students' => 15,
            'location' => 'Studio A',
            'active' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/mobile/v1/student/courses');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'courses' => [
                            '*' => [
                                'id',
                                'name',
                                'description',
                                'instructor_name',
                                'price',
                                'schedule',
                                'available_spots',
                                'can_enroll',
                            ],
                        ],
                        'pagination',
                    ],
                ]);
    }

    /** @test */
    public function it_can_get_course_details()
    {
        $course = Course::create([
            'name' => 'Ballet Basics',
            'description' => 'Learn the fundamentals of ballet',
            'instructor_id' => $this->admin->id,
            'school_id' => $this->school->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'schedule' => 'Monday, Wednesday 18:00-19:30',
            'price' => 120.00,
            'max_students' => 15,
            'location' => 'Studio A',
            'active' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/mobile/v1/student/courses/{$course->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'course' => [
                            'id',
                            'name',
                            'description',
                            'instructor_name',
                            'price',
                            'schedule',
                            'location',
                            'available_spots',
                            'can_enroll',
                            'is_enrolled',
                        ],
                    ],
                ]);
    }

    /** @test */
    public function it_can_enroll_in_a_course()
    {
        $course = Course::create([
            'name' => 'Ballet Basics',
            'description' => 'Learn the fundamentals of ballet',
            'instructor_id' => $this->admin->id,
            'school_id' => $this->school->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'schedule' => 'Monday, Wednesday 18:00-19:30',
            'price' => 120.00,
            'max_students' => 15,
            'location' => 'Studio A',
            'active' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/mobile/v1/student/enrollments', [
            'course_id' => $course->id,
            'enrollment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'enrollment' => [
                            'id',
                            'course_name',
                            'enrollment_date',
                            'status',
                        ],
                    ],
                ]);

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_can_get_enrolled_courses()
    {
        $course = Course::create([
            'name' => 'Ballet Basics',
            'description' => 'Learn the fundamentals of ballet',
            'instructor_id' => $this->admin->id,
            'school_id' => $this->school->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'schedule' => 'Monday, Wednesday 18:00-19:30',
            'price' => 120.00,
            'max_students' => 15,
            'location' => 'Studio A',
            'active' => true,
        ]);

        CourseEnrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/mobile/v1/student/courses/enrolled/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'enrolled_courses' => [
                            '*' => [
                                'id',
                                'course_name',
                                'instructor_name',
                                'schedule',
                                'enrollment_date',
                                'status',
                            ],
                        ],
                    ],
                ]);
    }

    /** @test */
    public function it_can_cancel_enrollment()
    {
        $course = Course::create([
            'name' => 'Ballet Basics',
            'description' => 'Learn the fundamentals of ballet',
            'instructor_id' => $this->admin->id,
            'school_id' => $this->school->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'schedule' => 'Monday, Wednesday 18:00-19:30',
            'price' => 120.00,
            'max_students' => 15,
            'location' => 'Studio A',
            'active' => true,
        ]);

        $enrollment = CourseEnrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson("/api/mobile/v1/student/enrollments/{$enrollment->id}/cancel", [
            'reason' => 'Schedule conflict',
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Enrollment cancelled successfully',
                ]);

        $this->assertDatabaseHas('course_enrollments', [
            'id' => $enrollment->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function it_cannot_enroll_in_full_course()
    {
        $course = Course::create([
            'name' => 'Ballet Basics',
            'description' => 'Learn the fundamentals of ballet',
            'instructor_id' => $this->admin->id,
            'school_id' => $this->school->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'schedule' => 'Monday, Wednesday 18:00-19:30',
            'price' => 120.00,
            'max_students' => 1, // Only 1 spot
            'location' => 'Studio A',
            'active' => true,
        ]);

        // Fill the course
        $otherStudent = User::create([
            'name' => 'Other Student',
            'first_name' => 'Other',
            'last_name' => 'Student',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'school_id' => $this->school->id,
            'active' => true,
        ]);

        CourseEnrollment::create([
            'user_id' => $otherStudent->id,
            'course_id' => $course->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/mobile/v1/student/enrollments', [
            'course_id' => $course->id,
            'enrollment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Course is full',
                ]);
    }

    /** @test */
    public function it_cannot_enroll_twice_in_same_course()
    {
        $course = Course::create([
            'name' => 'Ballet Basics',
            'description' => 'Learn the fundamentals of ballet',
            'instructor_id' => $this->admin->id,
            'school_id' => $this->school->id,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'schedule' => 'Monday, Wednesday 18:00-19:30',
            'price' => 120.00,
            'max_students' => 15,
            'location' => 'Studio A',
            'active' => true,
        ]);

        // First enrollment
        CourseEnrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        // Try to enroll again
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/mobile/v1/student/enrollments', [
            'course_id' => $course->id,
            'enrollment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Already enrolled in this course',
                ]);
    }
}