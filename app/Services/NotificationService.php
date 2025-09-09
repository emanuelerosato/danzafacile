<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\School;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            Mail::send('emails.welcome', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email, $user->full_name)
                        ->subject('Benvenuto in ' . ($user->school->name ?? 'Scuola di Danza'))
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info("Welcome email sent to user {$user->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send welcome email to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send course enrollment confirmation
     */
    public function sendEnrollmentConfirmation(CourseEnrollment $enrollment): bool
    {
        try {
            $user = $enrollment->user;
            $course = $enrollment->course;

            Mail::send('emails.enrollment-confirmation', [
                'user' => $user,
                'course' => $course,
                'enrollment' => $enrollment
            ], function ($message) use ($user, $course) {
                $message->to($user->email, $user->full_name)
                        ->subject("Iscrizione confermata: {$course->name}")
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info("Enrollment confirmation sent to user {$user->id} for course {$course->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send enrollment confirmation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(Payment $payment): bool
    {
        try {
            $user = $payment->user;
            
            Mail::send('emails.payment-confirmation', [
                'user' => $user,
                'payment' => $payment
            ], function ($message) use ($user, $payment) {
                $message->to($user->email, $user->full_name)
                        ->subject("Pagamento ricevuto - €{$payment->amount}")
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info("Payment confirmation sent to user {$user->id} for payment {$payment->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send payment confirmation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send course reminder to students
     */
    public function sendCourseReminder(Course $course, string $reminderType = 'upcoming'): int
    {
        $count = 0;
        $enrollments = $course->enrollments()->active()->with('user')->get();

        foreach ($enrollments as $enrollment) {
            try {
                Mail::send("emails.course-reminder-{$reminderType}", [
                    'user' => $enrollment->user,
                    'course' => $course,
                    'enrollment' => $enrollment
                ], function ($message) use ($enrollment, $course, $reminderType) {
                    $subject = $reminderType === 'upcoming' ? 
                              "Promemoria: {$course->name} inizia presto!" :
                              "Promemoria: {$course->name}";
                              
                    $message->to($enrollment->user->email, $enrollment->user->full_name)
                            ->subject($subject)
                            ->from(config('mail.from.address'), config('mail.from.name'));
                });

                $count++;
                Log::info("Course reminder sent to user {$enrollment->user->id} for course {$course->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send course reminder: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Send admin notification for new enrollment
     */
    public function sendAdminEnrollmentNotification(CourseEnrollment $enrollment): bool
    {
        try {
            $course = $enrollment->course;
            $school = $course->school;
            $admins = $school->admins;

            foreach ($admins as $admin) {
                Mail::send('emails.admin.new-enrollment', [
                    'admin' => $admin,
                    'enrollment' => $enrollment,
                    'course' => $course,
                    'student' => $enrollment->user
                ], function ($message) use ($admin, $course) {
                    $message->to($admin->email, $admin->full_name)
                            ->subject("Nuova iscrizione: {$course->name}")
                            ->from(config('mail.from.address'), config('mail.from.name'));
                });
            }

            Log::info("Admin notification sent for enrollment {$enrollment->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send admin enrollment notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send document approval notification
     */
    public function sendDocumentStatusNotification($document, string $status): bool
    {
        try {
            $user = $document->user;
            $statusText = $status === 'approved' ? 'approvato' : 'respinto';

            Mail::send('emails.document-status', [
                'user' => $user,
                'document' => $document,
                'status' => $status,
                'statusText' => $statusText
            ], function ($message) use ($user, $document, $statusText) {
                $message->to($user->email, $user->full_name)
                        ->subject("Documento {$statusText}: {$document->title}")
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info("Document status notification sent to user {$user->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send document status notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send bulk email to school students
     */
    public function sendBulkNotification(School $school, string $subject, string $message, array $userIds = []): int
    {
        $count = 0;
        
        $query = $school->students();
        if (!empty($userIds)) {
            $query->whereIn('id', $userIds);
        }
        
        $users = $query->get();

        foreach ($users as $user) {
            try {
                Mail::send('emails.bulk-notification', [
                    'user' => $user,
                    'customMessage' => $message,
                    'school' => $school
                ], function ($mailMessage) use ($user, $subject) {
                    $mailMessage->to($user->email, $user->full_name)
                               ->subject($subject)
                               ->from(config('mail.from.address'), config('mail.from.name'));
                });

                $count++;
                Log::info("Bulk notification sent to user {$user->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send bulk notification to user {$user->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Queue email for later sending (for better performance)
     */
    public function queueEmail(string $emailType, array $data): void
    {
        // This would typically use Laravel's job queue system
        // For now, we'll implement a simple database queue
        \DB::table('queued_emails')->insert([
            'email_type' => $emailType,
            'data' => json_encode($data),
            'attempts' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}