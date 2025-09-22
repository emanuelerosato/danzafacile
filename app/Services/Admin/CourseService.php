<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CourseService
{
    protected $scheduleService;
    protected $mediaService;

    public function __construct(
        ScheduleService $scheduleService,
        CourseMediaService $mediaService
    ) {
        $this->scheduleService = $scheduleService;
        $this->mediaService = $mediaService;
    }

    /**
     * Create a new course
     */
    public function createCourse(array $data): Course
    {
        DB::beginTransaction();

        try {
            $course = Course::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'difficulty_level' => $data['difficulty_level'] ?? null,
                'duration_weeks' => $data['duration_weeks'] ?? null,
                'max_students' => $data['max_students'] ?? null,
                'price' => $data['price'] ?? null,
                'status' => $data['status'] ?? 'draft',
                'school_id' => auth()->user()->school_id,
            ]);

            // Handle schedule data
            if (isset($data['schedule_slots'])) {
                $this->scheduleService->saveScheduleSlots($course, $data['schedule_slots']);
            }

            // Handle media uploads
            if (isset($data['media'])) {
                $this->mediaService->handleMediaUploads($course, $data['media']);
            }

            // Handle equipment and objectives
            if (isset($data['equipment'])) {
                $this->saveListField($course, 'equipment', $data['equipment']);
            }

            if (isset($data['objectives'])) {
                $this->saveListField($course, 'objectives', $data['objectives']);
            }

            DB::commit();

            Log::info('Course created successfully', ['course_id' => $course->id]);

            return $course;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create course', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update an existing course
     */
    public function updateCourse(Course $course, array $data): Course
    {
        DB::beginTransaction();

        try {
            $course->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                'difficulty_level' => $data['difficulty_level'] ?? null,
                'duration_weeks' => $data['duration_weeks'] ?? null,
                'max_students' => $data['max_students'] ?? null,
                'price' => $data['price'] ?? null,
                'status' => $data['status'] ?? $course->status,
            ]);

            // Update schedule data
            if (isset($data['schedule_slots'])) {
                $this->scheduleService->updateScheduleSlots($course, $data['schedule_slots']);
            }

            // Update media
            if (isset($data['media'])) {
                $this->mediaService->updateCourseMedia($course, $data['media']);
            }

            // Update equipment and objectives
            if (isset($data['equipment'])) {
                $this->updateListField($course, 'equipment', $data['equipment']);
            }

            if (isset($data['objectives'])) {
                $this->updateListField($course, 'objectives', $data['objectives']);
            }

            DB::commit();

            Log::info('Course updated successfully', ['course_id' => $course->id]);

            return $course->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update course', ['course_id' => $course->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a course
     */
    public function deleteCourse(Course $course): bool
    {
        DB::beginTransaction();

        try {
            // Delete associated media files
            $this->mediaService->deleteAllCourseMedia($course);

            // Delete schedule slots
            $this->scheduleService->deleteAllScheduleSlots($course);

            // Delete the course
            $course->delete();

            DB::commit();

            Log::info('Course deleted successfully', ['course_id' => $course->id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete course', ['course_id' => $course->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get course with all related data for editing
     */
    public function getCourseForEdit(Course $course): array
    {
        // Load relationships
        $course->load(['scheduleSlots', 'media', 'enrollments']);

        // Get schedule data formatted for the form
        $scheduleData = $this->scheduleService->getFormattedScheduleData($course);

        // Get available rooms
        $rooms = Room::where('school_id', auth()->user()->school_id)
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get();

        // Get current equipment and objectives
        $equipment = $this->getListField($course, 'equipment');
        $objectives = $this->getListField($course, 'objectives');

        return [
            'course' => $course,
            'scheduleData' => $scheduleData,
            'rooms' => $rooms,
            'equipment' => $equipment,
            'objectives' => $objectives,
        ];
    }

    /**
     * Duplicate a course
     */
    public function duplicateCourse(Course $originalCourse): Course
    {
        DB::beginTransaction();

        try {
            $duplicatedCourse = Course::create([
                'name' => $originalCourse->name . ' (Copia)',
                'description' => $originalCourse->description,
                'type' => $originalCourse->type,
                'difficulty_level' => $originalCourse->difficulty_level,
                'duration_weeks' => $originalCourse->duration_weeks,
                'max_students' => $originalCourse->max_students,
                'price' => $originalCourse->price,
                'status' => 'draft', // Always start as draft
                'school_id' => $originalCourse->school_id,
                'equipment' => $originalCourse->equipment,
                'objectives' => $originalCourse->objectives,
            ]);

            // Duplicate schedule slots
            $this->scheduleService->duplicateScheduleSlots($originalCourse, $duplicatedCourse);

            // Duplicate media (create copies of files)
            $this->mediaService->duplicateCourseMedia($originalCourse, $duplicatedCourse);

            DB::commit();

            Log::info('Course duplicated successfully', [
                'original_id' => $originalCourse->id,
                'duplicate_id' => $duplicatedCourse->id
            ]);

            return $duplicatedCourse;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to duplicate course', [
                'original_id' => $originalCourse->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get courses with filtering and pagination
     */
    public function getFilteredCourses(Request $request)
    {
        $query = Course::where('school_id', auth()->user()->school_id);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('difficulty_level')) {
            $query->where('difficulty_level', $request->difficulty_level);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        return $query->with(['scheduleSlots', 'enrollments'])->paginate(12);
    }

    /**
     * Save list field (equipment, objectives)
     */
    protected function saveListField(Course $course, string $field, array $items): void
    {
        $filteredItems = array_filter($items, fn($item) => !empty(trim($item)));
        $course->update([$field => $filteredItems]);
    }

    /**
     * Update list field
     */
    protected function updateListField(Course $course, string $field, array $items): void
    {
        $this->saveListField($course, $field, $items);
    }

    /**
     * Get list field data
     */
    protected function getListField(Course $course, string $field): array
    {
        return $course->$field ?? [];
    }

    /**
     * Check if user can manage course
     */
    public function userCanManage(Course $course): bool
    {
        return $course->school_id === auth()->user()->school_id;
    }

    /**
     * Get course statistics
     */
    public function getCourseStatistics(Course $course): array
    {
        return [
            'total_enrollments' => $course->enrollments()->count(),
            'active_enrollments' => $course->enrollments()->where('status', 'active')->count(),
            'completed_enrollments' => $course->enrollments()->where('status', 'completed')->count(),
            'revenue' => $course->enrollments()->where('status', 'active')->sum('amount_paid'),
            'attendance_rate' => $this->calculateAttendanceRate($course),
        ];
    }

    /**
     * Calculate attendance rate for course
     */
    protected function calculateAttendanceRate(Course $course): float
    {
        // Implementation would depend on your attendance tracking system
        // This is a placeholder
        return 0.0;
    }
}