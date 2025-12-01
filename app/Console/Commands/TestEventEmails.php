<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventPayment;
use App\Mail\GuestMagicLinkMail;
use App\Mail\EventRegistrationConfirmationMail;
use App\Mail\EventPaymentConfirmationMail;
use App\Mail\EventReminderMail;
use App\Mail\ThankYouPostEventMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEventEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:event-emails {--email=test@example.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all event email templates by sending them to a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $testEmail = $this->option('email');

        $this->info("Testing Event Email System");
        $this->info("Sending test emails to: {$testEmail}");
        $this->newLine();

        // Get or create test data
        $event = Event::where('is_public', true)->first();

        if (!$event) {
            $this->error('No public event found. Please create a public event first.');
            return 1;
        }

        $user = User::where('is_guest', true)->first();

        if (!$user) {
            $this->error('No guest user found. Creating a test guest user...');
            $user = User::create([
                'name' => 'Test Guest User',
                'email' => $testEmail,
                'password' => bcrypt('password'),
                'is_guest' => true,
                'role' => 'user',
                'active' => true,
            ]);
            $user->generateGuestToken();
        }

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$registration) {
            $this->info('No registration found. Creating test registration...');
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'school_id' => $event->school_id,
                'status' => 'confirmed',
                'registration_date' => now(),
                'qr_code_token' => \Str::random(64),
            ]);
        }

        $payment = EventPayment::where('event_registration_id', $registration->id)->first();

        if (!$payment) {
            $this->info('No payment found. Creating test payment...');
            $payment = EventPayment::create([
                'event_id' => $event->id,
                'event_registration_id' => $registration->id,
                'user_id' => $user->id,
                'school_id' => $event->school_id,
                'amount' => 25.00,
                'currency' => 'EUR',
                'status' => 'completed',
                'payment_method' => 'stripe',
                'transaction_id' => 'TEST-' . time(),
                'paid_at' => now(),
                'payer_email' => $user->email,
                'payer_name' => $user->name,
            ]);
        }

        $this->newLine();
        $this->info("Using test data:");
        $this->line("Event: {$event->name} (ID: {$event->id})");
        $this->line("User: {$user->name} (ID: {$user->id})");
        $this->line("Registration: #{$registration->id}");
        $this->line("Payment: #{$payment->id}");
        $this->newLine();

        // Test 1: Magic Link Email
        $this->info('1. Testing Magic Link Email...');
        try {
            $magicLink = $user->getMagicLoginLink();
            Mail::to($testEmail)->send(new GuestMagicLinkMail($user, $event, $magicLink));
            $this->line('   ✓ Magic Link Email sent successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }

        // Test 2: Registration Confirmation Email
        $this->info('2. Testing Registration Confirmation Email...');
        try {
            Mail::to($testEmail)->send(new EventRegistrationConfirmationMail($user, $event, $registration));
            $this->line('   ✓ Registration Confirmation Email sent successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }

        // Test 3: Payment Confirmation Email
        $this->info('3. Testing Payment Confirmation Email...');
        try {
            Mail::to($testEmail)->send(new EventPaymentConfirmationMail($user, $event, $registration, $payment));
            $this->line('   ✓ Payment Confirmation Email sent successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }

        // Test 4: Event Reminder Email
        $this->info('4. Testing Event Reminder Email...');
        try {
            Mail::to($testEmail)->send(new EventReminderMail($user, $event, $registration));
            $this->line('   ✓ Event Reminder Email sent successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }

        // Test 5: Thank You Post-Event Email
        $this->info('5. Testing Thank You Post-Event Email...');
        try {
            Mail::to($testEmail)->send(new ThankYouPostEventMail($user, $event, $registration));
            $this->line('   ✓ Thank You Email sent successfully');
        } catch (\Exception $e) {
            $this->error('   ✗ Failed: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('✅ All test emails have been sent!');
        $this->newLine();
        $this->line('Check your emails at:');

        if (config('mail.mailer') === 'log') {
            $this->line('Mail driver is set to "log". Check: storage/logs/laravel.log');
        } else {
            $this->line("Email: {$testEmail}");
            $this->line('Mailpit UI: http://localhost:8026');
        }

        return 0;
    }
}
