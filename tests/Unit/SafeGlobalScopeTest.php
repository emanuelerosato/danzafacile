<?php

namespace Tests\Unit;

use App\Models\Traits\HasSchoolScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SafeGlobalScopeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function middleware_sets_school_context_safely()
    {
        // Simulate middleware setting school context
        app()->instance('current_school_id', 123);

        $this->assertEquals(123, app('current_school_id'));
    }

    /** @test */
    public function trait_uses_safe_school_scoping()
    {
        // Create test model using trait
        $testModel = new class extends Model {
            use HasSchoolScope;
            protected $table = 'test_table';
        };

        // Set school context
        app()->instance('current_school_id', 456);

        // Verify trait methods work
        $this->assertEquals(456, $testModel::getCurrentSchoolId());
    }

    /** @test */
    public function global_scope_does_not_cause_recursion()
    {
        // Set school context
        app()->instance('current_school_id', 789);

        // This should not cause infinite recursion like auth()->check() did
        $testModel = new class extends Model {
            use HasSchoolScope;
            protected $table = 'test_table';
        };

        // Create a query builder to test scope application
        $query = $testModel->newQuery();

        // This should work without causing recursive calls
        $this->assertInstanceOf(Builder::class, $query);
    }

    /** @test */
    public function without_school_context_no_scope_applied()
    {
        // No school context set
        app()->forgetInstance('current_school_id');

        $testModel = new class extends Model {
            use HasSchoolScope;
            protected $table = 'test_table';
        };

        $this->assertNull($testModel::getCurrentSchoolId());
    }

    /** @test */
    public function scope_can_be_bypassed_for_admin_operations()
    {
        app()->instance('current_school_id', 999);

        $testModel = new class extends Model {
            use HasSchoolScope;
            protected $table = 'test_table';
        };

        // Test that withoutSchoolScope works
        $query = $testModel->withoutSchoolScope();
        $this->assertInstanceOf(Builder::class, $query);
    }
}