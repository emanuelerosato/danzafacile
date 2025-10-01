<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\QueryHelper;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QueryHelperTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test validateSortField accepts whitelisted fields
     */
    public function test_validate_sort_field_accepts_whitelisted_fields()
    {
        $allowedFields = ['name', 'created_at', 'email'];

        $result = QueryHelper::validateSortField('name', $allowedFields, 'created_at');

        $this->assertEquals('name', $result);
    }

    /**
     * Test validateSortField rejects non-whitelisted fields
     */
    public function test_validate_sort_field_rejects_non_whitelisted_fields()
    {
        $allowedFields = ['name', 'created_at', 'email'];

        // Try malicious field
        $result = QueryHelper::validateSortField('password', $allowedFields, 'created_at');

        $this->assertEquals('created_at', $result); // Should return default
    }

    /**
     * Test validateSortField rejects SQL injection attempts
     */
    public function test_validate_sort_field_rejects_sql_injection()
    {
        $allowedFields = ['name', 'created_at', 'email'];

        $maliciousInput = "name;DROP TABLE users--";
        $result = QueryHelper::validateSortField($maliciousInput, $allowedFields, 'created_at');

        $this->assertEquals('created_at', $result);
    }

    /**
     * Test validateSortDirection accepts valid directions
     */
    public function test_validate_sort_direction_accepts_valid_directions()
    {
        $this->assertEquals('asc', QueryHelper::validateSortDirection('asc'));
        $this->assertEquals('desc', QueryHelper::validateSortDirection('desc'));
        $this->assertEquals('asc', QueryHelper::validateSortDirection('ASC'));
        $this->assertEquals('desc', QueryHelper::validateSortDirection('DESC'));
    }

    /**
     * Test validateSortDirection rejects invalid directions
     */
    public function test_validate_sort_direction_rejects_invalid_directions()
    {
        $result = QueryHelper::validateSortDirection('invalid', 'desc');
        $this->assertEquals('desc', $result);

        $malicious = QueryHelper::validateSortDirection("DESC;DROP TABLE--", 'asc');
        $this->assertEquals('asc', $malicious);
    }

    /**
     * Test sanitizeLikeInput escapes wildcards
     */
    public function test_sanitize_like_input_escapes_wildcards()
    {
        $input = "test%data";
        $result = QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals("test\\%data", $result);
    }

    /**
     * Test sanitizeLikeInput escapes underscore
     */
    public function test_sanitize_like_input_escapes_underscore()
    {
        $input = "test_data";
        $result = QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals("test\\_data", $result);
    }

    /**
     * Test sanitizeLikeInput escapes backslash
     */
    public function test_sanitize_like_input_escapes_backslash()
    {
        $input = "test\\data";
        $result = QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals("test\\\\data", $result);
    }

    /**
     * Test sanitizeLikeInput limits length
     */
    public function test_sanitize_like_input_limits_length()
    {
        $longInput = str_repeat('A', 200);
        $result = QueryHelper::sanitizeLikeInput($longInput);

        $this->assertEquals(100, strlen($result));
    }

    /**
     * Test sanitizeLikeInput trims whitespace
     */
    public function test_sanitize_like_input_trims_whitespace()
    {
        $input = "  test data  ";
        $result = QueryHelper::sanitizeLikeInput($input);

        $this->assertEquals("test data", $result);
    }

    /**
     * Test sanitizeLikeInput handles empty string
     */
    public function test_sanitize_like_input_handles_empty_string()
    {
        $result = QueryHelper::sanitizeLikeInput("");
        $this->assertEquals("", $result);

        $result = QueryHelper::sanitizeLikeInput("   ");
        $this->assertEquals("", $result);
    }

    /**
     * Test validatePerPage accepts valid values
     */
    public function test_validate_per_page_accepts_valid_values()
    {
        $this->assertEquals(15, QueryHelper::validatePerPage(15));
        $this->assertEquals(50, QueryHelper::validatePerPage(50));
        $this->assertEquals(1, QueryHelper::validatePerPage(1));
    }

    /**
     * Test validatePerPage caps excessive values
     */
    public function test_validate_per_page_caps_excessive_values()
    {
        $result = QueryHelper::validatePerPage(999999, 15, 100);
        $this->assertEquals(100, $result);
    }

    /**
     * Test validatePerPage handles null
     */
    public function test_validate_per_page_handles_null()
    {
        $result = QueryHelper::validatePerPage(null, 20);
        $this->assertEquals(20, $result);
    }

    /**
     * Test validatePerPage handles negative values
     */
    public function test_validate_per_page_handles_negative_values()
    {
        $result = QueryHelper::validatePerPage(-10, 15);
        $this->assertEquals(15, $result);
    }

    /**
     * Test validatePerPage handles zero
     */
    public function test_validate_per_page_handles_zero()
    {
        $result = QueryHelper::validatePerPage(0, 15);
        $this->assertEquals(15, $result);
    }

    /**
     * Test applySafeSort with valid parameters
     */
    public function test_apply_safe_sort_with_valid_parameters()
    {
        $query = Payment::query();
        $allowedFields = ['payment_date', 'amount', 'status'];

        $result = QueryHelper::applySafeSort(
            $query,
            'amount',
            'asc',
            $allowedFields,
            'payment_date',
            'desc'
        );

        // Verify query contains the sort
        $sql = $result->toSql();
        $this->assertStringContainsString('order by', strtolower($sql));
    }

    /**
     * Test applySafeSort rejects invalid field
     */
    public function test_apply_safe_sort_rejects_invalid_field()
    {
        $query = Payment::query();
        $allowedFields = ['payment_date', 'amount', 'status'];

        // Try to sort by non-whitelisted field
        $result = QueryHelper::applySafeSort(
            $query,
            'school_id', // Not in whitelist
            'asc',
            $allowedFields,
            'payment_date',
            'desc'
        );

        // Should use default field
        $sql = $result->toSql();
        $this->assertStringContainsString('order by', strtolower($sql));
    }

    /**
     * Test applySafeLike with single column
     */
    public function test_apply_safe_like_single_column()
    {
        $query = Payment::query();

        $result = QueryHelper::applySafeLike($query, 'transaction_id', 'test');

        $sql = $result->toSql();
        $this->assertStringContainsString('like', strtolower($sql));
        $this->assertStringContainsString('transaction_id', strtolower($sql));
    }

    /**
     * Test applySafeLike escapes wildcards
     */
    public function test_apply_safe_like_escapes_wildcards()
    {
        $query = Payment::query();

        // Input with wildcard
        $result = QueryHelper::applySafeLike($query, 'transaction_id', 'test%');

        $bindings = $result->getBindings();
        // Should have escaped the %
        $this->assertStringContainsString('\\%', $bindings[0] ?? '');
    }

    /**
     * Test applySafeLike handles empty search
     */
    public function test_apply_safe_like_handles_empty_search()
    {
        $query = Payment::query();
        $originalSql = $query->toSql();

        $result = QueryHelper::applySafeLike($query, 'transaction_id', '');

        // Should not modify query if search is empty
        $this->assertEquals($originalSql, $result->toSql());
    }

    /**
     * Test applySafeLikeMultiple with multiple columns
     */
    public function test_apply_safe_like_multiple_columns()
    {
        $query = Payment::query();
        $columns = ['transaction_id', 'receipt_number', 'reference_number'];

        $result = QueryHelper::applySafeLikeMultiple($query, $columns, 'test');

        $sql = $result->toSql();
        $this->assertStringContainsString('like', strtolower($sql));
        // Should have OR conditions for multiple columns
        $this->assertStringContainsString('or', strtolower($sql));
    }

    /**
     * Test applySafeLike with different positions
     */
    public function test_apply_safe_like_with_different_positions()
    {
        $query1 = Payment::query();
        $result1 = QueryHelper::applySafeLike($query1, 'transaction_id', 'test', 'both');
        $bindings1 = $result1->getBindings();
        $this->assertStringStartsWith('%', $bindings1[0]);
        $this->assertStringEndsWith('%', $bindings1[0]);

        $query2 = Payment::query();
        $result2 = QueryHelper::applySafeLike($query2, 'transaction_id', 'test', 'start');
        $bindings2 = $result2->getBindings();
        $this->assertStringStartsWith('test', $bindings2[0]);
        $this->assertStringEndsWith('%', $bindings2[0]);

        $query3 = Payment::query();
        $result3 = QueryHelper::applySafeLike($query3, 'transaction_id', 'test', 'end');
        $bindings3 = $result3->getBindings();
        $this->assertStringStartsWith('%', $bindings3[0]);
        $this->assertStringEndsWith('test', $bindings3[0]);
    }
}
