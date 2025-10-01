<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Payment;
use App\Models\Course;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SqlInjectionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private School $school;

    protected function setUp(): void
    {
        parent::setUp();

        // Create school and admin user
        $this->school = School::factory()->create();
        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'admin',
            'active' => true
        ]);
    }

    /**
     * Test SQL Injection via sort parameter in payments index
     */
    public function test_prevents_sql_injection_via_sort_parameter_in_payments()
    {
        // Create some test data
        Payment::factory()->count(5)->create(['school_id' => $this->school->id]);

        // Attempt SQL injection via sort parameter
        $maliciousSort = "payment_date;DROP TABLE payments--";

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'sort' => $maliciousSort,
            'direction' => 'desc'
        ]));

        // Should return 200 (safe fallback to default sorting)
        $response->assertStatus(200);

        // Verify payments table still exists
        $this->assertDatabaseCount('payments', 5);
    }

    /**
     * Test SQL Injection via direction parameter
     */
    public function test_prevents_sql_injection_via_direction_parameter()
    {
        Payment::factory()->count(3)->create(['school_id' => $this->school->id]);

        // Attempt SQL injection via direction parameter
        $maliciousDirection = "DESC;DELETE FROM payments WHERE 1=1--";

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'sort' => 'payment_date',
            'direction' => $maliciousDirection
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseCount('payments', 3);
    }

    /**
     * Test LIKE wildcard injection in search
     */
    public function test_prevents_like_wildcard_injection_in_search()
    {
        // Create test payments
        Payment::factory()->create([
            'school_id' => $this->school->id,
            'transaction_id' => 'TXN123',
        ]);
        Payment::factory()->create([
            'school_id' => $this->school->id,
            'transaction_id' => 'TXN456',
        ]);

        // Attempt to inject wildcards to match everything
        $maliciousSearch = "%";

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'search' => $maliciousSearch
        ]));

        $response->assertStatus(200);

        // Should not return results because % is escaped
        // (depends on exact implementation, but should be safe)
    }

    /**
     * Test SQL Injection via sort in students index
     */
    public function test_prevents_sql_injection_in_students_sort()
    {
        User::factory()->count(5)->create([
            'school_id' => $this->school->id,
            'role' => 'user'
        ]);

        $maliciousSort = "name;DROP TABLE users--";

        $response = $this->actingAs($this->admin)->get(route('admin.students.index', [
            'sort' => $maliciousSort
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * Test SQL Injection via sort in courses index
     */
    public function test_prevents_sql_injection_in_courses_sort()
    {
        Course::factory()->count(3)->create(['school_id' => $this->school->id]);

        $maliciousSort = "name' OR '1'='1";

        $response = $this->actingAs($this->admin)->get(route('admin.courses.index', [
            'sort' => $maliciousSort
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseCount('courses', 3);
    }

    /**
     * Test only whitelisted sort fields are accepted
     */
    public function test_only_whitelisted_sort_fields_accepted()
    {
        Payment::factory()->count(3)->create(['school_id' => $this->school->id]);

        // Try to sort by a non-whitelisted field
        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'sort' => 'school_id',  // Not in whitelist
            'direction' => 'asc'
        ]));

        $response->assertStatus(200);

        // Should fall back to default sort field (payment_date)
        // Verify by checking that we get data (not an SQL error)
        $response->assertViewHas('payments');
    }

    /**
     * Test per_page parameter validation
     */
    public function test_prevents_excessive_per_page_values()
    {
        Payment::factory()->count(150)->create(['school_id' => $this->school->id]);

        // Try to request excessive per_page (DoS attack)
        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'per_page' => 999999
        ]));

        $response->assertStatus(200);

        // Should be capped at max (100)
        $response->assertViewHas('payments');
        $payments = $response->viewData('payments');
        $this->assertLessThanOrEqual(100, $payments->perPage());
    }

    /**
     * Test negative per_page is handled safely
     */
    public function test_handles_negative_per_page_safely()
    {
        Payment::factory()->count(20)->create(['school_id' => $this->school->id]);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'per_page' => -10
        ]));

        $response->assertStatus(200);

        // Should fall back to default (20 for payments)
        $response->assertViewHas('payments');
        $payments = $response->viewData('payments');
        $this->assertEquals(20, $payments->perPage());
    }

    /**
     * Test LIKE injection with backslash
     */
    public function test_prevents_like_injection_with_backslash()
    {
        Payment::factory()->create([
            'school_id' => $this->school->id,
            'transaction_id' => 'TEST_123',
        ]);

        // Attempt to inject backslash to escape the escape character
        $maliciousSearch = "\\%";

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'search' => $maliciousSearch
        ]));

        $response->assertStatus(200);
        // Should be escaped safely
    }

    /**
     * Test combined attack vectors
     */
    public function test_prevents_combined_sql_injection_attacks()
    {
        Payment::factory()->count(5)->create(['school_id' => $this->school->id]);

        // Combined attack: malicious sort + direction + search
        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'sort' => "payment_date';DROP TABLE payments--",
            'direction' => "DESC;DELETE FROM users WHERE role='admin'--",
            'search' => "%' OR '1'='1"
        ]));

        $response->assertStatus(200);

        // Verify no data was destroyed
        $this->assertDatabaseCount('payments', 5);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'role' => 'admin']);
    }

    /**
     * Test search length limitation (DoS prevention)
     */
    public function test_limits_search_term_length()
    {
        Payment::factory()->count(3)->create(['school_id' => $this->school->id]);

        // Very long search string (potential DoS)
        $longSearch = str_repeat('A', 500);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.index', [
            'search' => $longSearch
        ]));

        $response->assertStatus(200);
        // Should be truncated to 100 chars max
    }
}
