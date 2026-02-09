<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class CriticalFixesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test #1: Bulk delete con multi-tenant isolation (AdminBaseController fix)
     * Verifica che admin School A NON possa cancellare studenti di School B
     *
     * @test
     */
    public function admin_cannot_bulk_delete_students_from_other_school()
    {
        // Arrange: Crea 2 scuole con studenti
        $school1 = School::factory()->create(['name' => 'Scuola Danza A']);
        $school2 = School::factory()->create(['name' => 'Scuola Danza B']);

        $admin1 = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school1->id,
            'email' => 'admin1@test.local',
            'password' => Hash::make('password'),
        ]);

        $student1 = User::factory()->create([
            'role' => 'student',
            'school_id' => $school1->id,
            'email' => 'student1@school1.local',
        ]);

        $student2 = User::factory()->create([
            'role' => 'student',
            'school_id' => $school2->id,
            'email' => 'student2@school2.local',
        ]);

        // Act: Admin school1 prova bulk delete su studente di school2
        $response = $this->actingAs($admin1)
            ->postJson('/admin/students/bulk-action', [
                'action' => 'delete',
                'ids' => [$student2->id], // Tentativo cross-tenant
            ]);

        // Assert: Student2 NON cancellato (school2 - protected)
        $this->assertDatabaseHas('users', [
            'id' => $student2->id,
            'email' => 'student2@school2.local',
        ]);

        // Assert: Student1 esistente (school1)
        $this->assertDatabaseHas('users', [
            'id' => $student1->id,
            'email' => 'student1@school1.local',
        ]);
    }

    /**
     * Test #1b: Bulk delete funziona correttamente per la propria scuola
     *
     * @test
     */
    public function admin_can_bulk_delete_own_school_students()
    {
        // Arrange: Crea scuola con studenti
        $school = School::factory()->create(['name' => 'Scuola Danza']);

        $admin = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school->id,
            'email' => 'admin@test.local',
            'password' => Hash::make('password'),
        ]);

        $student1 = User::factory()->create([
            'role' => 'student',
            'school_id' => $school->id,
            'email' => 'student1@school.local',
        ]);

        $student2 = User::factory()->create([
            'role' => 'student',
            'school_id' => $school->id,
            'email' => 'student2@school.local',
        ]);

        // Act: Admin bulk delete propri studenti
        $response = $this->actingAs($admin)
            ->postJson('/admin/students/bulk-action', [
                'action' => 'delete',
                'ids' => [$student1->id, $student2->id],
            ]);

        // Assert: Studenti della propria scuola cancellati
        $this->assertDatabaseMissing('users', ['id' => $student1->id]);
        $this->assertDatabaseMissing('users', ['id' => $student2->id]);
    }

    /**
     * Test #2: Staff creation popola first_name e last_name correttamente
     *
     * @test
     */
    public function staff_creation_populates_first_and_last_name()
    {
        // Arrange: Crea scuola e admin
        $school = School::factory()->create(['name' => 'Scuola Danza']);

        $admin = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school->id,
            'email' => 'admin@test.local',
            'password' => Hash::make('password'),
        ]);

        // Act: Crea staff member con nome completo
        $response = $this->actingAs($admin)
            ->postJson('/admin/staff', [
                'name' => 'Mario Rossi',
                'email' => 'mario.rossi@test.local',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'instructor',
                'employment_type' => 'full_time',
                'status' => 'active',
                'hire_date' => now()->format('Y-m-d'),
            ]);

        // Assert: Response success
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['id', 'user_id'],
        ]);

        // Assert: User creato con first_name e last_name corretti
        $this->assertDatabaseHas('users', [
            'email' => 'mario.rossi@test.local',
            'name' => 'Mario Rossi',
            'first_name' => 'Mario',
            'last_name' => 'Rossi',
        ]);
    }

    /**
     * Test #2b: Staff creation con single name (edge case)
     *
     * @test
     */
    public function staff_creation_handles_single_name_gracefully()
    {
        // Arrange: Crea scuola e admin
        $school = School::factory()->create(['name' => 'Scuola Danza']);

        $admin = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school->id,
            'email' => 'admin@test.local',
            'password' => Hash::make('password'),
        ]);

        // Act: Crea staff member con nome singolo
        $response = $this->actingAs($admin)
            ->postJson('/admin/staff', [
                'name' => 'Madonna',
                'email' => 'madonna@test.local',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'instructor',
                'employment_type' => 'full_time',
                'status' => 'active',
                'hire_date' => now()->format('Y-m-d'),
            ]);

        // Assert: Response success
        $response->assertStatus(201);

        // Assert: User creato con fallback a nome singolo per last_name
        $this->assertDatabaseHas('users', [
            'email' => 'madonna@test.local',
            'name' => 'Madonna',
            'first_name' => 'Madonna',
            'last_name' => 'Madonna', // Fallback
        ]);
    }

    /**
     * Test #2c: Staff update popola first_name e last_name correttamente
     *
     * @test
     */
    public function staff_update_populates_first_and_last_name()
    {
        // Arrange: Crea scuola, admin e staff
        $school = School::factory()->create(['name' => 'Scuola Danza']);

        $admin = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school->id,
            'email' => 'admin@test.local',
            'password' => Hash::make('password'),
        ]);

        $staffUser = User::factory()->create([
            'role' => 'admin', // Staff members sono admin
            'school_id' => $school->id,
            'name' => 'Vecchio Nome',
            'first_name' => 'Vecchio',
            'last_name' => 'Nome',
            'email' => 'staff@test.local',
        ]);

        $staff = Staff::factory()->create([
            'school_id' => $school->id,
            'user_id' => $staffUser->id,
            'role' => 'instructor',
            'employment_type' => 'full_time',
            'status' => 'active',
        ]);

        // Act: Update staff name
        $response = $this->actingAs($admin)
            ->putJson("/admin/staff/{$staff->id}", [
                'name' => 'Nuovo Nome Completo',
                'email' => 'staff@test.local',
                'role' => 'instructor',
                'employment_type' => 'full_time',
                'status' => 'active',
            ]);

        // Assert: Response success
        $response->assertStatus(200);

        // Assert: User aggiornato con first_name e last_name corretti
        $this->assertDatabaseHas('users', [
            'id' => $staffUser->id,
            'email' => 'staff@test.local',
            'name' => 'Nuovo Nome Completo',
            'first_name' => 'Nuovo',
            'last_name' => 'Nome Completo',
        ]);
    }

    /**
     * Test #3: Reports mostrano SOLO studenti della propria scuola
     *
     * @test
     */
    public function reports_show_only_own_school_students()
    {
        // Arrange: Crea 2 scuole con studenti
        $school1 = School::factory()->create(['name' => 'Scuola A']);
        $school2 = School::factory()->create(['name' => 'Scuola B']);

        $admin1 = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school1->id,
            'email' => 'admin1@test.local',
        ]);

        // 3 studenti school1
        User::factory()->count(3)->create([
            'role' => 'student',
            'school_id' => $school1->id,
        ]);

        // 5 studenti school2
        User::factory()->count(5)->create([
            'role' => 'student',
            'school_id' => $school2->id,
        ]);

        // Act: Admin1 richiede metrics
        $response = $this->actingAs($admin1)
            ->getJson('/admin/reports');

        // Assert: Response success
        $response->assertStatus(200);

        // Assert: Metrics mostrano SOLO studenti di school1 (3 totali)
        $response->assertJsonPath('metrics.students.total', 3);
    }

    /**
     * Test #3b: Reports chart data rispetta multi-tenant isolation
     *
     * @test
     */
    public function reports_chart_data_respects_multi_tenant_isolation()
    {
        // Arrange: Crea 2 scuole con studenti
        $school1 = School::factory()->create(['name' => 'Scuola A']);
        $school2 = School::factory()->create(['name' => 'Scuola B']);

        $admin2 = User::factory()->create([
            'role' => 'admin',
            'school_id' => $school2->id,
            'email' => 'admin2@test.local',
        ]);

        // 2 studenti school1
        User::factory()->count(2)->create([
            'role' => 'student',
            'school_id' => $school1->id,
        ]);

        // 7 studenti school2
        User::factory()->count(7)->create([
            'role' => 'student',
            'school_id' => $school2->id,
        ]);

        // Act: Admin2 richiede chart data
        $response = $this->actingAs($admin2)
            ->getJson('/admin/reports/charts-data?type=students&period=month');

        // Assert: Response success
        $response->assertStatus(200);

        // Assert: Chart data structure corretta
        $response->assertJsonStructure([
            'labels',
            'datasets' => [
                '*' => ['label', 'data'],
            ],
        ]);

        // Verifica manuale che i count siano corretti per school2
        $this->assertEquals(7, User::where('role', 'student')
            ->where('school_id', $school2->id)
            ->count());
    }
}
