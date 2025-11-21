<?php

namespace App\Http\Controllers\Api;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Attendance API Controller
 *
 * Handles attendance tracking for Flutter app including:
 * - View attendance history
 * - Check-in to sessions (manual or QR code)
 * - Generate QR codes for check-in
 * - Attendance statistics
 */
class AttendanceController extends BaseApiController
{
    /**
     * Get user's attendance history
     */
    public function myAttendance(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        $params = $this->getPaginationParams($request);

        $query = Attendance::where('user_id', $user->id)
            ->with(['course', 'event', 'markedBy']);

        // Filter by course
        if ($request->has('course_id')) {
            $query->where('course_id', $request->get('course_id'));
        }

        // Filter by event
        if ($request->has('event_id')) {
            $query->where('event_id', $request->get('event_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('attendance_date', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date')) {
            $query->where('attendance_date', '<=', $request->get('to_date'));
        }

        $query->orderBy('attendance_date', 'desc');
        $attendances = $query->paginate($params['per_page'], ['*'], 'page', $params['page']);

        // Transform data
        $attendances->getCollection()->transform(function ($attendance) {
            return [
                'id' => $attendance->id,
                'attendance_date' => $attendance->attendance_date->toISOString(),
                'status' => $attendance->status,
                'notes' => $attendance->notes,
                'marked_at' => $attendance->marked_at?->toISOString(),
                'marked_by' => $attendance->markedBy ? [
                    'id' => $attendance->markedBy->id,
                    'name' => $attendance->markedBy->name,
                ] : null,
                'course' => $attendance->course ? [
                    'id' => $attendance->course->id,
                    'name' => $attendance->course->name,
                ] : null,
                'event' => $attendance->event ? [
                    'id' => $attendance->event->id,
                    'name' => $attendance->event->name,
                ] : null,
            ];
        });

        return $this->paginatedResponse($attendances, 'Attendance history retrieved successfully');
    }

    /**
     * Get attendance statistics for user
     */
    public function myStats(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        $totalSessions = Attendance::where('user_id', $user->id)->count();
        $presentSessions = Attendance::where('user_id', $user->id)
            ->where('status', 'present')
            ->count();

        $thisMonthTotal = Attendance::where('user_id', $user->id)
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->count();

        $thisMonthPresent = Attendance::where('user_id', $user->id)
            ->where('status', 'present')
            ->whereMonth('attendance_date', now()->month)
            ->whereYear('attendance_date', now()->year)
            ->count();

        $attendanceRate = $totalSessions > 0 ? ($presentSessions / $totalSessions) * 100 : 0;
        $monthlyRate = $thisMonthTotal > 0 ? ($thisMonthPresent / $thisMonthTotal) * 100 : 0;

        // Get recent attendance (last 10)
        $recentAttendance = Attendance::where('user_id', $user->id)
            ->with(['course', 'event'])
            ->orderBy('attendance_date', 'desc')
            ->take(10)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'attendance_date' => $attendance->attendance_date->toDateString(),
                    'status' => $attendance->status,
                    'session_name' => $attendance->course?->name ?? $attendance->event?->name ?? 'N/A',
                ];
            });

        return $this->successResponse([
            'stats' => [
                'total_sessions' => $totalSessions,
                'present_sessions' => $presentSessions,
                'attendance_rate' => round($attendanceRate, 1),
                'this_month_total' => $thisMonthTotal,
                'this_month_present' => $thisMonthPresent,
                'monthly_rate' => round($monthlyRate, 1),
            ],
            'recent_attendance' => $recentAttendance,
        ], 'Attendance statistics retrieved successfully');
    }

    /**
     * Generate QR code for check-in
     */
    public function generateQrCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:course,event',
            'session_id' => 'required|integer',
            'session_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = $this->getAuthenticatedUser();
        $type = $request->get('type');
        $sessionId = $request->get('session_id');
        $sessionDate = $request->get('session_date');

        // Validate session exists and user has access
        if ($type === 'course') {
            $course = Course::find($sessionId);
            if (!$course || !$this->validateTenantAccess($course)) {
                return $this->notFoundResponse('Course');
            }

            // Check if user is enrolled
            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $sessionId)
                ->where('status', 'active')
                ->first();

            if (!$enrollment) {
                return $this->forbiddenResponse('Not enrolled in this course');
            }

        } elseif ($type === 'event') {
            $event = Event::find($sessionId);
            if (!$event || !$this->validateTenantAccess($event)) {
                return $this->notFoundResponse('Event');
            }

            // Check if user is registered
            $registration = $event->registrations()->where('user_id', $user->id)->first();
            if (!$registration) {
                return $this->forbiddenResponse('Not registered for this event');
            }
        }

        // Generate unique QR code data
        $qrData = [
            'user_id' => $user->id,
            'type' => $type,
            'session_id' => $sessionId,
            'session_date' => $sessionDate,
            'token' => Str::random(32),
            'expires_at' => now()->addHours(2)->timestamp, // QR expires in 2 hours
        ];

        // In a real implementation, you might want to store this token temporarily in cache/database
        $encodedData = base64_encode(json_encode($qrData));

        return $this->successResponse([
            'qr_code_data' => $encodedData,
            'expires_at' => now()->addHours(2)->toISOString(),
            'session_info' => [
                'type' => $type,
                'session_id' => $sessionId,
                'session_date' => $sessionDate,
                'session_name' => $type === 'course' ? $course->name ?? 'Unknown Course' : $event->name ?? 'Unknown Event',
            ]
        ], 'QR code generated successfully');
    }

    /**
     * Manual check-in (without QR code)
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:course,event',
            'session_id' => 'required|integer',
            'session_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $user = $this->getAuthenticatedUser();
        $type = $request->get('type');
        $sessionId = $request->get('session_id');
        $sessionDate = $request->get('session_date');

        // Check if attendance already exists for this session
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where($type . '_id', $sessionId)
            ->whereDate('attendance_date', $sessionDate)
            ->first();

        if ($existingAttendance) {
            return $this->errorResponse('Already checked in for this session', 400);
        }

        // Validate session and user access (same logic as QR generation)
        if ($type === 'course') {
            $course = Course::find($sessionId);
            if (!$course || !$this->validateTenantAccess($course)) {
                return $this->notFoundResponse('Course');
            }

            $enrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $sessionId)
                ->where('status', 'active')
                ->first();

            if (!$enrollment) {
                return $this->forbiddenResponse('Not enrolled in this course');
            }

            $sessionName = $course->name;
        } else {
            $event = Event::find($sessionId);
            if (!$event || !$this->validateTenantAccess($event)) {
                return $this->notFoundResponse('Event');
            }

            $registration = $event->registrations()->where('user_id', $user->id)->first();
            if (!$registration) {
                return $this->forbiddenResponse('Not registered for this event');
            }

            $sessionName = $event->name;
        }

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $user->id,
            $type . '_id' => $sessionId,
            'attendance_date' => $sessionDate,
            'status' => 'present',
            'marked_at' => now(),
            'marked_by_user_id' => $user->id, // Self check-in
            'notes' => $request->get('notes'),
        ]);

        return $this->successResponse([
            'attendance' => [
                'id' => $attendance->id,
                'attendance_date' => $attendance->attendance_date->toISOString(),
                'status' => $attendance->status,
                'marked_at' => $attendance->marked_at->toISOString(),
                'notes' => $attendance->notes,
            ],
            'session' => [
                'type' => $type,
                'id' => $sessionId,
                'name' => $sessionName,
                'date' => $sessionDate,
            ]
        ], 'Successfully checked in', 201);
    }

    /**
     * QR Code check-in (admin scans student's QR)
     */
    public function qrCheckIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $currentUser = $this->getAuthenticatedUser();

        // Only admins can scan QR codes
        if (!$currentUser->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can scan QR codes');
        }

        try {
            $qrData = json_decode(base64_decode($request->get('qr_data')), true);
        } catch (\Exception $e) {
            return $this->errorResponse('Invalid QR code', 400);
        }

        // Validate QR code structure
        if (!isset($qrData['user_id'], $qrData['type'], $qrData['session_id'], $qrData['token'], $qrData['expires_at'])) {
            return $this->errorResponse('Invalid QR code format', 400);
        }

        // Check if QR code is expired
        if ($qrData['expires_at'] < now()->timestamp) {
            return $this->errorResponse('QR code has expired', 400);
        }

        $userId = $qrData['user_id'];
        $type = $qrData['type'];
        $sessionId = $qrData['session_id'];
        $sessionDate = $qrData['session_date'];

        // Validate user exists and belongs to same school
        $user = \App\Models\User::find($userId);
        if (!$user || $user->school_id !== $currentUser->school_id) {
            return $this->errorResponse('Invalid user or different school', 400);
        }

        // Check if attendance already exists
        $existingAttendance = Attendance::where('user_id', $userId)
            ->where($type . '_id', $sessionId)
            ->whereDate('attendance_date', $sessionDate)
            ->first();

        if ($existingAttendance) {
            return $this->errorResponse('User already checked in for this session', 400);
        }

        // Get session name
        $sessionName = '';
        if ($type === 'course') {
            $course = Course::find($sessionId);
            $sessionName = $course?->name ?? 'Unknown Course';
        } else {
            $event = Event::find($sessionId);
            $sessionName = $event?->name ?? 'Unknown Event';
        }

        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $userId,
            $type . '_id' => $sessionId,
            'attendance_date' => $sessionDate,
            'status' => 'present',
            'marked_at' => now(),
            'marked_by_user_id' => $currentUser->id, // Admin marked attendance
            'notes' => 'Checked in via QR code',
        ]);

        return $this->successResponse([
            'attendance' => [
                'id' => $attendance->id,
                'attendance_date' => $attendance->attendance_date->toISOString(),
                'status' => $attendance->status,
                'marked_at' => $attendance->marked_at->toISOString(),
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'session' => [
                'type' => $type,
                'id' => $sessionId,
                'name' => $sessionName,
            ]
        ], 'QR check-in successful', 201);
    }

    /**
     * Get upcoming sessions for check-in (courses/events user is enrolled/registered for)
     */
    public function upcomingSessions(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        $upcomingSessions = [];

        // Get enrolled courses with upcoming sessions
        $enrolledCourses = CourseEnrollment::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('course')
            ->get();

        foreach ($enrolledCourses as $enrollment) {
            $course = $enrollment->course;
            if ($course && $course->end_date > now()) {
                $upcomingSessions[] = [
                    'type' => 'course',
                    'id' => $course->id,
                    'name' => $course->name,
                    'schedule' => $course->schedule,
                    'location' => $course->location,
                    'next_session' => now()->next('monday')->toDateString(), // Simplified - you might have actual session dates
                ];
            }
        }

        // Get registered events
        $registeredEvents = \App\Models\EventRegistration::where('user_id', $user->id)
            ->whereHas('event', function($q) {
                $q->where('start_date', '>', now());
            })
            ->with('event')
            ->get();

        foreach ($registeredEvents as $registration) {
            $event = $registration->event;
            $upcomingSessions[] = [
                'type' => 'event',
                'id' => $event->id,
                'name' => $event->name,
                'start_date' => $event->start_date->toISOString(),
                'location' => $event->location,
                'next_session' => $event->start_date->toDateString(),
            ];
        }

        // Sort by next session date
        usort($upcomingSessions, function($a, $b) {
            return strtotime($a['next_session']) - strtotime($b['next_session']);
        });

        return $this->successResponse([
            'upcoming_sessions' => array_slice($upcomingSessions, 0, 10), // Limit to next 10
        ], 'Upcoming sessions retrieved successfully');
    }

    /**
     * Student QR check-in (student scans lesson QR code)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function studentQrCheckIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "qr_code" => "required|string",
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $currentUser = $this->getAuthenticatedUser();
        $qrCode = $request->get("qr_code");

        // Parse QR code format: LESSON_{lesson_id}_{date}
        $parts = explode("_", $qrCode);
        
        if (count($parts) !== 3 || $parts[0] !== "LESSON") {
            return $this->errorResponse("QR code non valido. Formato atteso: LESSON_ID_DATE", 400);
        }

        $lessonId = $parts[1];
        $lessonDate = $parts[2];

        try {
            $attendanceDate = \Carbon\Carbon::createFromFormat("Ymd", $lessonDate)->format("Y-m-d");
        } catch (\Exception $e) {
            return $this->errorResponse("Formato data QR non valido", 400);
        }

        $existingAttendance = Attendance::where("user_id", $currentUser->id)
            ->where("attendable_type", "App\\Models\\Course")
            ->where("attendable_id", $lessonId)
            ->whereDate("date", $attendanceDate)
            ->first();

        if ($existingAttendance) {
            return $this->errorResponse("Check-in già effettuato per questa lezione", 400);
        }

        $attendance = Attendance::create([
            "user_id" => $currentUser->id,
            "school_id" => $currentUser->school_id,
            "attendable_type" => "App\\Models\\Course",
            "attendable_id" => $lessonId,
            "date" => $attendanceDate,
            "check_in_time" => now()->format("H:i:s"),
            "status" => "present",
            "marked_by" => $currentUser->id,
            "notes" => "Self check-in via QR code scan",
        ]);

        return $this->successResponse([
            "attendance" => [
                "id" => $attendance->id,
                "date" => $attendance->date,
                "check_in_time" => $attendance->check_in_time,
                "status" => $attendance->status,
            ],
            "message" => "Check-in completato con successo!",
        ], "Check-in successful", 201);
    }
}
