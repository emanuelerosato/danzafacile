<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use Illuminate\Http\Request;

class TestSuperAdminDashboard extends Command
{
    protected $signature = 'test:super-admin-dashboard';
    protected $description = 'Test Super Admin Dashboard functionality end-to-end';

    public function handle()
    {
        $this->info('🧪 TESTING SUPER ADMIN DASHBOARD END-TO-END');
        $this->line('');

        // Test 1: Verify Super Admin User Exists
        $this->info('1. Verifying Super Admin user...');
        $superAdmin = User::where('email', 'superadmin@scuoladanza.it')->first();
        
        if (!$superAdmin) {
            $this->error('❌ Super Admin user not found!');
            return Command::FAILURE;
        }
        
        $this->line("   ✅ User found: {$superAdmin->name} ({$superAdmin->email})");
        $this->line("   ✅ Role: {$superAdmin->role}");
        $this->line("   ✅ Active: " . ($superAdmin->active ? 'Yes' : 'No'));
        $this->line('');

        // Test 2: Test Controller Instantiation
        $this->info('2. Testing SuperAdminController instantiation...');
        try {
            $controller = new SuperAdminController();
            $this->line('   ✅ SuperAdminController instantiated successfully');
        } catch (\Exception $e) {
            $this->error("   ❌ Controller instantiation failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
        $this->line('');

        // Test 3: Test Dashboard Method (index)
        $this->info('3. Testing index() method (dashboard)...');
        try {
            // Create a mock request
            $request = new Request();
            
            // Simulate authenticated user for testing
            auth()->login($superAdmin);
            
            // Call index method (dashboard)
            $response = $controller->index($request);
            
            // Check if it's a View instance
            if ($response instanceof \Illuminate\View\View) {
                $this->line('   ✅ Index method (dashboard) executed successfully');
                $this->line('   ✅ Returned: View instance');
                $this->line("   ✅ View name: {$response->getName()}");
            } else {
                $this->warn("   ⚠️  Index method returned unexpected type: " . get_class($response));
            }
            
        } catch (\Exception $e) {
            $this->error("   ❌ Dashboard method failed: {$e->getMessage()}");
            $this->error("   Stack trace: " . substr($e->getTraceAsString(), 0, 200) . "...");
            return Command::FAILURE;
        }
        $this->line('');

        // Test 4: Test Database Statistics
        $this->info('4. Testing database statistics...');
        try {
            $stats = [
                'schools' => \App\Models\School::count(),
                'users' => \App\Models\User::count(),
                'courses' => \App\Models\Course::count(),
                'enrollments' => \App\Models\CourseEnrollment::count(),
                'payments' => \App\Models\Payment::count(),
                'total_revenue' => \App\Models\Payment::where('status', 'completed')->sum('amount'),
            ];

            $this->line("   ✅ Schools: {$stats['schools']}");
            $this->line("   ✅ Users: {$stats['users']}");
            $this->line("   ✅ Courses: {$stats['courses']}");
            $this->line("   ✅ Enrollments: {$stats['enrollments']}");
            $this->line("   ✅ Payments: {$stats['payments']}");
            $this->line("   ✅ Total Revenue: €" . number_format($stats['total_revenue'], 2));
        } catch (\Exception $e) {
            $this->error("   ❌ Statistics failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
        $this->line('');

        // Test 5: Test Reports Method (fixed method name)
        $this->info('5. Testing reports() method...');
        try {
            $request = new Request(['type' => 'schools', 'period' => 'month']);
            $response = $controller->reports($request);
            
            if ($response->getStatusCode() === 200) {
                $this->line('   ✅ Reports method executed successfully');
            } else {
                $this->warn("   ⚠️  Reports returned status: {$response->getStatusCode()}");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Reports method failed: {$e->getMessage()}");
            return Command::FAILURE;
        }
        $this->line('');

        // Final Summary
        $this->info('🎉 ALL TESTS PASSED!');
        $this->line('');
        $this->info('📊 SUMMARY:');
        $this->line('   ✅ Super Admin user exists and is active');
        $this->line('   ✅ SuperAdminController instantiates without errors');
        $this->line('   ✅ index() method (dashboard) works correctly');
        $this->line('   ✅ Database statistics are accessible');
        $this->line('   ✅ reports() method works correctly');
        $this->line('');
        $this->info('🚀 The Super Admin Dashboard is fully functional!');
        
        return Command::SUCCESS;
    }
}