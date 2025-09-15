<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\School;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAdminTest extends TestCase
{
    use RefreshDatabase;

    protected $school;
    protected $admin;
    protected $student;
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

        // Create test admin
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

        // Create test student
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

        $this->token = $this->admin->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function it_can_get_admin_dashboard()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/mobile/v1/admin/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'stats' => [
                            'students_total',
                            'students_active',
                            'courses_active',
                            'revenue_this_month',
                        ],
                        'recent_enrollments',
                        'pending_payments',
                        'upcoming_events',
                    ],
                ]);
    }

    /** @test */
    public function it_can_list_students()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/mobile/v1/admin/students');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'students' => [
                            '*' => [
                                'id',
                                'name',
                                'email',
                                'phone',
                                'active',
                                'created_at',
                            ],
                        ],
                        'pagination',
                    ],
                ]);
    }

    /** @test */
    public function it_can_create_student()
    {
        $studentData = [
            'name' => 'New Student',
            'email' => 'newstudent@example.com',
            'first_name' => 'New',
            'last_name' => 'Student',
            'password' => 'password123',
            'phone' => '+1234567890',
            'date_of_birth' => '1995-01-01',
            'address' => 'Student Street 123',
            'emergency_contact' => 'Parent: +0987654321',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/mobile/v1/admin/students', $studentData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'student' => [
                            'id',
                            'name',
                            'email',
                            'phone',
                            'active',
                        ],
                    ],
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newstudent@example.com',
            'role' => 'user',
            'school_id' => $this->school->id,
        ]);
    }

    /** @test */
    public function it_can_update_student()
    {
        $updateData = [
            'name' => 'Updated Student Name',
            'phone' => '+9999999999',
            'active' => false,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/mobile/v1/admin/students/{$this->student->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'student' => [
                            'id',
                            'name',
                            'phone',
                            'active',
                        ],
                    ],
                ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->student->id,
            'name' => 'Updated Student Name',
            'phone' => '+9999999999',
            'active' => false,
        ]);
    }

    /** @test */
    public function it_can_delete_student()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/mobile/v1/admin/students/{$this->student->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Student deleted successfully',
                ]);

        $this->assertDatabaseMissing('users', [
            'id' => $this->student->id,
        ]);
    }

    /** @test */
    public function it_can_list_courses()
    {
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
        ])->getJson('/api/mobile/v1/admin/courses');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'courses' => [
                            '*' => [
                                'id',
                                'name',
                                'instructor_name',
                                'price',
                                'enrolled_students',
                                'max_students',
                                'active',
                            ],
                        ],
                        'pagination',
                    ],
                ]);
    }

    /** @test */
    public function it_can_create_course()
    {
        $courseData = [
            'name' => 'Advanced Ballet',
            'description' => 'Advanced ballet techniques',
            'instructor_id' => $this->admin->id,
            'start_date' => now()->addDays(14)->format('Y-m-d'),
            'end_date' => now()->addDays(44)->format('Y-m-d'),
            'schedule' => 'Tuesday, Thursday 19:00-20:30',
            'price' => 150.00,
            'max_students' => 12,
            'location' => 'Studio B',
            'duration_weeks' => 8,
            'difficulty_level' => 'advanced',
            'active' => true,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/mobile/v1/admin/courses', $courseData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'course' => [
                            'id',
                            'name',
                            'price',
                            'max_students',
                            'active',
                        ],
                    ],
                ]);

        $this->assertDatabaseHas('courses', [
            'name' => 'Advanced Ballet',
            'school_id' => $this->school->id,
            'instructor_id' => $this->admin->id,
        ]);
    }

    /** @test */
    public function it_can_toggle_course_status()
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
        ])->postJson("/api/mobile/v1/admin/courses/{$course->id}/toggle-status");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'course' => [
                            'id',
                            'name',
                            'active',
                        ],
                    ],
                ]);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'active' => false,
        ]);
    }

    /** @test */
    public function it_can_get_course_statistics()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/mobile/v1/admin/courses/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'stats' => [
                            'total_courses',
                            'active_courses',
                            'total_enrollments',
                            'revenue_this_month',
                        ],
                    ],
                ]);
    }

    /** @test */
    public function student_cannot_access_admin_endpoints()
    {
        $studentToken = $this->student->createToken('student-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $studentToken,
            'Accept' => 'application/json',
        ])->getJson('/api/mobile/v1/admin/dashboard');

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Only administrators can access this dashboard',
                ]);
    }

    /** @test */
    public function it_enforces_multi_tenant_security()
    {
        // Create another school and admin
        $otherSchool = School::create([
            'name' => 'Other School',
            'description' => 'Another school',
            'address' => 'Other Street 456',
            'city' => 'Other City',
            'postal_code' => '54321',
            'phone' => '+9876543210',
            'email' => 'other@school.com',
            'active' => true,
        ]);

        $otherStudent = User::create([
            'name' => 'Other Student',
            'first_name' => 'Other',
            'last_name' => 'Student',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'school_id' => $otherSchool->id,
            'active' => true,
        ]);

        // Admin from first school should not see students from second school
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/mobile/v1/admin/students/{$otherStudent->id}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Access denied to this student',
                ]);
    }
}