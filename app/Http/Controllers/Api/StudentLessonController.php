<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StudentLessonController extends Controller
{
    /**
     * Get upcoming lessons for authenticated student
     */
    public function upcoming(Request $request): JsonResponse
    {
        $days = $request->query('days', 7);

        // Get enrolled course IDs for this student
        $courseIds = $request->user()
            ->enrollments()
            ->pluck('course_id');

        // Fetch upcoming lessons
        $lessons = Lesson::query()
            ->with(['course', 'instructor', 'room'])
            ->whereIn('course_id', $courseIds)
            ->upcoming($days)
            ->get()
            ->map(function ($lesson) {
                return $this->transformLesson($lesson);
            });

        return response()->json([
            'success' => true,
            'data' => $lessons,
            'meta' => [
                'count' => $lessons->count(),
            ],
        ]);
    }

    /**
     * Get all lessons for authenticated student (with optional course filter)
     */
    public function index(Request $request): JsonResponse
    {
        $courseId = $request->query('course_id');

        $courseIds = $request->user()
            ->enrollments()
            ->pluck('course_id');

        $query = Lesson::query()
            ->with(['course', 'instructor', 'room'])
            ->whereIn('course_id', $courseIds)
            ->orderBy('lesson_date', 'desc')
            ->orderBy('start_time', 'desc');

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        $lessons = $query->get()->map(function ($lesson) {
            return $this->transformLesson($lesson);
        });

        return response()->json([
            'success' => true,
            'data' => $lessons,
            'meta' => [
                'count' => $lessons->count(),
            ],
        ]);
    }

    /**
     * Get lesson by ID
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $courseIds = $request->user()
            ->enrollments()
            ->pluck('course_id');

        $lesson = Lesson::query()
            ->with(['course', 'instructor', 'room'])
            ->whereIn('course_id', $courseIds)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->transformLesson($lesson),
        ]);
    }

    /**
     * Get lessons by specific date
     */
    public function byDate(Request $request, string $date): JsonResponse
    {
        try {
            $targetDate = Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Formato data non valido. Usa YYYY-MM-DD',
            ], 400);
        }

        $courseIds = $request->user()
            ->enrollments()
            ->pluck('course_id');

        $lessons = Lesson::query()
            ->with(['course', 'instructor', 'room'])
            ->whereIn('course_id', $courseIds)
            ->byDate($targetDate)
            ->orderBy('start_time')
            ->get()
            ->map(function ($lesson) {
                return $this->transformLesson($lesson);
            });

        return response()->json([
            'success' => true,
            'data' => $lessons,
            'meta' => [
                'date' => $targetDate->toDateString(),
                'count' => $lessons->count(),
            ],
        ]);
    }

    /**
     * Transform lesson to API format
     */
    private function transformLesson(Lesson $lesson): array
    {
        return [
            'id' => (string) $lesson->id,
            'course_id' => (string) $lesson->course_id,
            'course_name' => $lesson->course->name,
            'instructor_id' => $lesson->instructor_id ? (string) $lesson->instructor_id : null,
            'instructor_name' => $lesson->instructor?->name,
            'room_id' => $lesson->room_id ? (string) $lesson->room_id : null,
            'room_name' => $lesson->room?->name,
            'lesson_date' => $lesson->lesson_date->toDateString(),
            'start_time' => $lesson->start_time,
            'end_time' => $lesson->end_time,
            'status' => $lesson->status,
            'notes' => $lesson->notes,
            'start_datetime' => $lesson->start_datetime->toIso8601String(),
            'end_datetime' => $lesson->end_datetime->toIso8601String(),
            'is_upcoming' => $lesson->is_upcoming,
            'is_today' => $lesson->is_today,
        ];
    }
}
