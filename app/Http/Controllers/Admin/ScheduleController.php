<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScheduleController extends AdminBaseController
{
    /**
     * Display the schedule overview
     */
    public function index(Request $request)
    {
        $currentWeek = $request->get('week', now()->startOfWeek()->format('Y-m-d'));
        $weekStart = Carbon::parse($currentWeek)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        // Get all courses for this school
        $courses = $this->school->courses()
            ->with(['instructor'])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Get all instructors
        $instructors = $this->school->users()
            ->where('role', User::ROLE_INSTRUCTOR)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        // Generate weekly schedule
        $weeklySchedule = $this->generateWeeklySchedule($courses, $weekStart, $weekEnd);

        // Get schedule statistics
        $stats = [
            'total_courses' => $courses->count(),
            'total_instructors' => $instructors->count(),
            'weekly_hours' => $this->calculateWeeklyHours($courses),
            'room_utilization' => $this->calculateRoomUtilization($courses),
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'schedule' => $weeklySchedule,
                'stats' => $stats
            ]);
        }

        return view('admin.schedules.index', compact(
            'courses',
            'instructors',
            'weeklySchedule',
            'stats',
            'weekStart',
            'weekEnd'
        ));
    }

    /**
     * Show course schedule details
     */
    public function show(Course $course)
    {
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        $course->load(['instructor', 'enrollments.user']);

        // Parse schedule string to structured data
        $scheduleData = $this->parseScheduleString($course->schedule);

        return view('admin.schedules.show', compact('course', 'scheduleData'));
    }

    /**
     * Show schedule management page
     */
    public function manage()
    {
        $courses = $this->school->courses()
            ->with(['instructor'])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $instructors = $this->school->users()
            ->where('role', User::ROLE_INSTRUCTOR)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $timeSlots = $this->generateTimeSlots();
        $rooms = $this->getAvailableRooms();

        return view('admin.schedules.manage', compact(
            'courses',
            'instructors',
            'timeSlots',
            'rooms'
        ));
    }

    /**
     * Update course schedule
     */
    public function updateCourseSchedule(Request $request, Course $course)
    {
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        $validated = $request->validate([
            'schedule' => 'required|string|max:500',
            'location' => 'nullable|string|max:255',
            'instructor_id' => 'nullable|exists:users,id'
        ]);

        $course->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Orario aggiornato con successo.',
                'course' => $course->fresh(['instructor'])
            ]);
        }

        return redirect()->route('admin.schedules.index')
                        ->with('success', 'Orario aggiornato con successo.');
    }

    /**
     * Generate weekly schedule view
     */
    private function generateWeeklySchedule($courses, $weekStart, $weekEnd)
    {
        $schedule = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];

        foreach ($days as $index => $day) {
            $schedule[$day] = [
                'name' => $dayNames[$index],
                'date' => $weekStart->copy()->addDays($index)->format('d/m'),
                'courses' => []
            ];
        }

        foreach ($courses as $course) {
            if (!$course->schedule) continue;

            $scheduleData = $this->parseScheduleString($course->schedule);

            foreach ($scheduleData as $session) {
                $dayKey = strtolower($session['day']);
                if (isset($schedule[$dayKey])) {
                    $schedule[$dayKey]['courses'][] = [
                        'id' => $course->id,
                        'name' => $course->name,
                        'instructor' => $course->instructor?->name ?? 'Nessun istruttore',
                        'time' => $session['time'],
                        'location' => $course->location ?? 'Sala principale',
                        'level' => $course->level,
                        'students_count' => $course->enrollments->count(),
                        'max_students' => $course->max_students
                    ];
                }
            }
        }

        // Sort courses by time for each day
        foreach ($schedule as $day => $data) {
            usort($schedule[$day]['courses'], function($a, $b) {
                return strcmp($a['time'], $b['time']);
            });
        }

        return $schedule;
    }

    /**
     * Parse schedule string (e.g., "Lun-Mer-Ven 18:00-19:30")
     */
    private function parseScheduleString($schedule)
    {
        if (!$schedule) return [];

        $result = [];
        $dayMapping = [
            'lun' => 'monday',
            'mar' => 'tuesday',
            'mer' => 'wednesday',
            'gio' => 'thursday',
            'ven' => 'friday',
            'sab' => 'saturday',
            'dom' => 'sunday'
        ];

        // Simple parsing for format: "Lun-Mer-Ven 18:00-19:30"
        $parts = explode(' ', $schedule);
        if (count($parts) >= 2) {
            $days = explode('-', strtolower($parts[0]));
            $time = $parts[1];

            foreach ($days as $day) {
                $day = trim($day);
                if (isset($dayMapping[$day])) {
                    $result[] = [
                        'day' => ucfirst($dayMapping[$day]),
                        'time' => $time
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Calculate total weekly hours
     */
    private function calculateWeeklyHours($courses)
    {
        $totalMinutes = 0;

        foreach ($courses as $course) {
            if (!$course->schedule) continue;

            $scheduleData = $this->parseScheduleString($course->schedule);

            foreach ($scheduleData as $session) {
                if (strpos($session['time'], '-') !== false) {
                    $timeParts = explode('-', $session['time']);
                    if (count($timeParts) === 2) {
                        $start = Carbon::createFromFormat('H:i', trim($timeParts[0]));
                        $end = Carbon::createFromFormat('H:i', trim($timeParts[1]));
                        $totalMinutes += $start->diffInMinutes($end);
                    }
                }
            }
        }

        return round($totalMinutes / 60, 1);
    }

    /**
     * Calculate room utilization percentage
     */
    private function calculateRoomUtilization($courses)
    {
        $rooms = [];
        $totalSlots = 0;
        $usedSlots = 0;

        foreach ($courses as $course) {
            if ($course->location) {
                $rooms[$course->location] = ($rooms[$course->location] ?? 0) + 1;
            }

            if ($course->schedule) {
                $scheduleData = $this->parseScheduleString($course->schedule);
                $usedSlots += count($scheduleData);
            }
        }

        // Estimate total available slots (7 days * 12 hours * number of rooms)
        $roomCount = max(1, count($rooms));
        $totalSlots = 7 * 12 * $roomCount;

        return $totalSlots > 0 ? round(($usedSlots / $totalSlots) * 100, 1) : 0;
    }

    /**
     * Generate time slots for scheduling
     */
    private function generateTimeSlots()
    {
        $slots = [];
        for ($hour = 8; $hour <= 22; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                $time = sprintf('%02d:%02d', $hour, $minute);
                $slots[] = $time;
            }
        }
        return $slots;
    }

    /**
     * Get available rooms/locations
     */
    private function getAvailableRooms()
    {
        $predefinedRooms = [
            'Sala Principale',
            'Sala A',
            'Sala B',
            'Studio 1',
            'Studio 2',
            'Sala Prove'
        ];

        // Get rooms used by existing courses
        $usedRooms = $this->school->courses()
            ->whereNotNull('location')
            ->pluck('location')
            ->unique()
            ->toArray();

        return array_unique(array_merge($predefinedRooms, $usedRooms));
    }

    /**
     * Export schedule to PDF/Excel
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'pdf');
        $week = $request->get('week', now()->startOfWeek()->format('Y-m-d'));

        $weekStart = Carbon::parse($week)->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $courses = $this->school->courses()
            ->with(['instructor'])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $weeklySchedule = $this->generateWeeklySchedule($courses, $weekStart, $weekEnd);

        if ($format === 'csv') {
            return $this->exportScheduleToCsv($weeklySchedule, $weekStart);
        }

        // For now, return CSV format
        return $this->exportScheduleToCsv($weeklySchedule, $weekStart);
    }

    /**
     * Export schedule to CSV
     */
    protected function exportScheduleToCsv($schedule, $weekStart)
    {
        $filename = 'orari_settimana_' . $weekStart->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($schedule, $weekStart) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Orari Scuola di Danza - Settimana del ' . $weekStart->format('d/m/Y')]);
            fputcsv($file, []);
            fputcsv($file, ['Giorno', 'Corso', 'Orario', 'Istruttore', 'Sala', 'Studenti']);

            foreach ($schedule as $day => $data) {
                foreach ($data['courses'] as $course) {
                    fputcsv($file, [
                        $data['name'],
                        $course['name'],
                        $course['time'],
                        $course['instructor'],
                        $course['location'],
                        $course['students_count'] . '/' . $course['max_students']
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}