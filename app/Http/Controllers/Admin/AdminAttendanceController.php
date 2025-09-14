<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AdminAttendanceController extends AdminBaseController
{
    /**
     * Display attendance dashboard
     */
    public function index(Request $request)
    {
        $query = $this->school->attendanceRecords()->with(['user', 'course', 'event', 'markedByUser']);

        $attendances = $this->getFilteredResults($query, $request, 20);

        // Get filter options for dropdowns
        $courses = $this->school->courses()->active()->orderBy('name')->get();
        $events = $this->school->events()->active()->orderBy('name')->get();

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Presenze recuperate con successo', [
                'html' => view('admin.attendance.partials.table', compact('attendances'))->render(),
                'pagination' => $attendances->links()->render()
            ]);
        }

        // Quick stats for header cards
        $today = now()->format('Y-m-d');
        $thisWeek = [now()->startOfWeek()->format('Y-m-d'), now()->endOfWeek()->format('Y-m-d')];
        $thisMonth = [now()->startOfMonth()->format('Y-m-d'), now()->endOfMonth()->format('Y-m-d')];

        $stats = [
            'today_present' => $this->school->attendanceRecords()
                ->forDate($today)
                ->present()
                ->count(),
            'today_total' => $this->school->attendanceRecords()
                ->forDate($today)
                ->count(),
            'week_avg_attendance' => $this->getWeeklyAttendanceRate(),
            'month_total_sessions' => $this->school->attendanceRecords()
                ->forDateRange($thisMonth[0], $thisMonth[1])
                ->count(),
        ];

        return view('admin.attendance.index', compact('attendances', 'courses', 'events', 'stats'));
    }

    /**
     * Show course attendance for a specific date
     */
    public function courseAttendance(Request $request, Course $course)
    {
        // Ensure course belongs to current school
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        $date = $request->get('date', today()->format('Y-m-d'));

        // Get enrolled students for this course
        $enrolledStudents = $course->courseEnrollments()
            ->with('user')
            ->where('status', 'active')
            ->get()
            ->pluck('user');

        // Get existing attendance records for this date
        $attendanceRecords = $course->attendanceRecords()
            ->with('user')
            ->forDate($date)
            ->get()
            ->keyBy('user_id');

        // Create attendance data combining enrolled students and records
        $attendanceData = $enrolledStudents->map(function ($student) use ($attendanceRecords, $course, $date) {
            $record = $attendanceRecords->get($student->id);

            return [
                'user' => $student,
                'attendance' => $record,
                'course' => $course,
                'date' => $date
            ];
        });

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Presenze recuperate con successo', [
                'html' => view('admin.attendance.partials.course-attendance', [
                    'attendanceData' => $attendanceData,
                    'course' => $course,
                    'date' => $date
                ])->render()
            ]);
        }

        return view('admin.attendance.course', compact('attendanceData', 'course', 'date'));
    }

    /**
     * Show event attendance for a specific date
     */
    public function eventAttendance(Request $request, Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        $date = $request->get('date', $event->start_date->format('Y-m-d'));

        // Get registered users for this event
        $registeredUsers = $event->registrations()
            ->with('user')
            ->whereIn('status', ['confirmed', 'registered'])
            ->get()
            ->pluck('user');

        // Get existing attendance records for this date
        $attendanceRecords = $event->attendanceRecords()
            ->with('user')
            ->forDate($date)
            ->get()
            ->keyBy('user_id');

        // Create attendance data
        $attendanceData = $registeredUsers->map(function ($user) use ($attendanceRecords, $event, $date) {
            $record = $attendanceRecords->get($user->id);

            return [
                'user' => $user,
                'attendance' => $record,
                'event' => $event,
                'date' => $date
            ];
        });

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Presenze recuperate con successo', [
                'html' => view('admin.attendance.partials.event-attendance', [
                    'attendanceData' => $attendanceData,
                    'event' => $event,
                    'date' => $date
                ])->render()
            ]);
        }

        return view('admin.attendance.event', compact('attendanceData', 'event', 'date'));
    }

    /**
     * Mark attendance for a user
     */
    public function mark(Request $request)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('school_id', $this->school->id)
            ],
            'date' => 'required|date',
            'course_id' => [
                'nullable',
                'integer',
                Rule::exists('courses', 'id')->where('school_id', $this->school->id)
            ],
            'event_id' => [
                'nullable',
                'integer',
                Rule::exists('events', 'id')->where('school_id', $this->school->id)
            ],
            'status' => 'required|in:present,absent,late,excused',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
            'method' => 'nullable|string|in:manual,qr_code,auto'
        ]);

        // Ensure either course_id or event_id is provided, not both
        if ((!$validated['course_id'] && !$validated['event_id']) ||
            ($validated['course_id'] && $validated['event_id'])) {
            return $this->jsonResponse(false, 'Specificare solo un corso o un evento.', [], 422);
        }

        // Create or update attendance record
        $attendance = Attendance::updateOrCreate([
            'user_id' => $validated['user_id'],
            'school_id' => $this->school->id,
            'course_id' => $validated['course_id'],
            'event_id' => $validated['event_id'],
            'attendance_date' => $validated['date']
        ], [
            'status' => $validated['status'],
            'check_in_time' => $validated['check_in_time'] ?? ($validated['status'] === 'present' ? now()->format('H:i:s') : null),
            'check_out_time' => $validated['check_out_time'],
            'notes' => $validated['notes'],
            'marked_by_method' => $validated['method'] ?? 'manual',
            'marked_by_user_id' => auth()->id()
        ]);

        $this->clearSchoolCache();

        $user = User::find($validated['user_id']);
        $subject = $validated['course_id'] ? Course::find($validated['course_id']) : Event::find($validated['event_id']);

        return $this->jsonResponse(true, "Presenza di {$user->name} aggiornata con successo.", [
            'attendance' => $attendance->load(['user', 'course', 'event', 'markedByUser'])
        ]);
    }

    /**
     * Bulk mark attendance
     */
    public function bulkMark(Request $request)
    {
        $validated = $request->validate([
            'attendances' => 'required|array',
            'attendances.*.user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('school_id', $this->school->id)
            ],
            'attendances.*.date' => 'required|date',
            'attendances.*.course_id' => [
                'nullable',
                'integer',
                Rule::exists('courses', 'id')->where('school_id', $this->school->id)
            ],
            'attendances.*.event_id' => [
                'nullable',
                'integer',
                Rule::exists('events', 'id')->where('school_id', $this->school->id)
            ],
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.check_in_time' => 'nullable|date_format:H:i',
            'attendances.*.notes' => 'nullable|string|max:500'
        ]);

        $processed = 0;
        $errors = [];

        foreach ($validated['attendances'] as $index => $attendanceData) {
            try {
                // Ensure either course_id or event_id is provided, not both
                if ((!$attendanceData['course_id'] && !$attendanceData['event_id']) ||
                    ($attendanceData['course_id'] && $attendanceData['event_id'])) {
                    $errors[] = "Record {$index}: Specificare solo un corso o un evento.";
                    continue;
                }

                Attendance::updateOrCreate([
                    'user_id' => $attendanceData['user_id'],
                    'school_id' => $this->school->id,
                    'course_id' => $attendanceData['course_id'],
                    'event_id' => $attendanceData['event_id'],
                    'attendance_date' => $attendanceData['date']
                ], [
                    'status' => $attendanceData['status'],
                    'check_in_time' => $attendanceData['check_in_time'] ?? ($attendanceData['status'] === 'present' ? now()->format('H:i:s') : null),
                    'notes' => $attendanceData['notes'] ?? null,
                    'marked_by_method' => 'manual',
                    'marked_by_user_id' => auth()->id()
                ]);

                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Record {$index}: " . $e->getMessage();
            }
        }

        $this->clearSchoolCache();

        if (count($errors) > 0) {
            return $this->jsonResponse(false, "{$processed} presenze elaborate. Errori: " . implode(', ', $errors), [], 422);
        }

        return $this->jsonResponse(true, "{$processed} presenze elaborate con successo.");
    }

    /**
     * Get attendance statistics for a user
     */
    public function userStats(User $user)
    {
        // Ensure user belongs to current school
        if ($user->school_id !== $this->school->id) {
            abort(404, 'Utente non trovato.');
        }

        $startDate = now()->subMonth();
        $endDate = now();

        $stats = [
            'total_sessions' => $user->attendanceRecords()
                ->forDateRange($startDate, $endDate)
                ->count(),
            'present_sessions' => $user->attendanceRecords()
                ->forDateRange($startDate, $endDate)
                ->present()
                ->count(),
            'absent_sessions' => $user->attendanceRecords()
                ->forDateRange($startDate, $endDate)
                ->absent()
                ->count(),
            'late_sessions' => $user->attendanceRecords()
                ->forDateRange($startDate, $endDate)
                ->late()
                ->count(),
            'attendance_rate' => 0
        ];

        if ($stats['total_sessions'] > 0) {
            $stats['attendance_rate'] = round(
                ($stats['present_sessions'] + $stats['late_sessions']) / $stats['total_sessions'] * 100,
                1
            );
        }

        return $this->jsonResponse(true, 'Statistiche recuperate con successo', compact('stats'));
    }

    /**
     * Export attendance data
     */
    public function export(Request $request)
    {
        $query = $this->school->attendanceRecords()->with(['user', 'course', 'event', 'markedByUser']);

        // Apply same filters as index
        $attendances = $this->getFilteredResults($query, $request, null);

        return $this->exportAttendancesToCSV($attendances);
    }

    /**
     * Delete attendance record
     */
    public function destroy(Attendance $attendance)
    {
        // Ensure attendance belongs to current school
        if ($attendance->school_id !== $this->school->id) {
            abort(404, 'Presenza non trovata.');
        }

        $userName = $attendance->user->name ?? 'Utente sconosciuto';
        $subjectName = $attendance->attendance_subject_name;

        $attendance->delete();
        $this->clearSchoolCache();

        return $this->jsonResponse(true, "Presenza di {$userName} per {$subjectName} eliminata con successo.");
    }

    /**
     * Apply search to attendance query
     */
    protected function applySearch($query, string $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->whereHas('user', function ($userQuery) use ($searchTerm) {
                $userQuery->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('email', 'like', "%{$searchTerm}%");
            })
            ->orWhereHas('course', function ($courseQuery) use ($searchTerm) {
                $courseQuery->where('name', 'like', "%{$searchTerm}%");
            })
            ->orWhereHas('event', function ($eventQuery) use ($searchTerm) {
                $eventQuery->where('name', 'like', "%{$searchTerm}%");
            })
            ->orWhere('notes', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Get weekly attendance rate
     */
    private function getWeeklyAttendanceRate(): float
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $totalSessions = $this->school->attendanceRecords()
            ->forDateRange($weekStart, $weekEnd)
            ->count();

        if ($totalSessions === 0) return 0;

        $presentSessions = $this->school->attendanceRecords()
            ->forDateRange($weekStart, $weekEnd)
            ->whereIn('status', ['present', 'late'])
            ->count();

        return round(($presentSessions / $totalSessions) * 100, 1);
    }

    /**
     * Export attendances to CSV
     */
    private function exportAttendancesToCSV($attendances)
    {
        $data = $attendances->map(function ($attendance) {
            return [
                $attendance->id,
                $attendance->user->name ?? 'N/A',
                $attendance->user->email ?? 'N/A',
                $attendance->attendance_subject_name,
                $attendance->attendance_type,
                $attendance->attendance_date->format('d/m/Y'),
                ucfirst($attendance->status),
                $attendance->check_in_time ?? '',
                $attendance->check_out_time ?? '',
                $attendance->duration_minutes ?? '',
                $attendance->notes ?? '',
                $attendance->marked_by_method,
                $attendance->markedByUser->name ?? 'Sistema',
                $attendance->created_at->format('d/m/Y H:i')
            ];
        })->toArray();

        $headers = [
            'ID', 'Nome Studente', 'Email', 'Corso/Evento', 'Tipo', 'Data',
            'Stato', 'Ora Ingresso', 'Ora Uscita', 'Durata (min)', 'Note',
            'Metodo', 'Segnato da', 'Creato il'
        ];

        $filename = 'presenze_' . str_replace(' ', '_', $this->school->name) . '_' . now()->format('Y-m-d') . '.csv';

        return $this->exportToCsv($data, $headers, $filename);
    }
}
