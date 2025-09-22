<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Models\ScheduleSlot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    /**
     * Save schedule slots for a course
     */
    public function saveScheduleSlots(Course $course, array $scheduleData): void
    {
        // Delete existing slots
        $course->scheduleSlots()->delete();

        // Create new slots
        foreach ($scheduleData as $slotData) {
            if ($this->isValidSlotData($slotData)) {
                ScheduleSlot::create([
                    'course_id' => $course->id,
                    'day' => $slotData['day'],
                    'start_time' => $slotData['start_time'],
                    'end_time' => $slotData['end_time'],
                    'room_id' => $slotData['room_id'] ?? null,
                ]);
            }
        }

        Log::info('Schedule slots saved', [
            'course_id' => $course->id,
            'slots_count' => count($scheduleData)
        ]);
    }

    /**
     * Update schedule slots for a course
     */
    public function updateScheduleSlots(Course $course, array $scheduleData): void
    {
        $this->saveScheduleSlots($course, $scheduleData);
    }

    /**
     * Delete all schedule slots for a course
     */
    public function deleteAllScheduleSlots(Course $course): void
    {
        $course->scheduleSlots()->delete();
        Log::info('All schedule slots deleted', ['course_id' => $course->id]);
    }

    /**
     * Duplicate schedule slots from one course to another
     */
    public function duplicateScheduleSlots(Course $originalCourse, Course $newCourse): void
    {
        $originalSlots = $originalCourse->scheduleSlots;

        foreach ($originalSlots as $slot) {
            ScheduleSlot::create([
                'course_id' => $newCourse->id,
                'day' => $slot->day,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'room_id' => $slot->room_id,
            ]);
        }

        Log::info('Schedule slots duplicated', [
            'original_course_id' => $originalCourse->id,
            'new_course_id' => $newCourse->id,
            'slots_count' => $originalSlots->count()
        ]);
    }

    /**
     * Get formatted schedule data for forms
     */
    public function getFormattedScheduleData(Course $course): array
    {
        return $course->scheduleSlots->map(function ($slot) {
            return [
                'id' => $slot->id,
                'day' => $slot->day,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'room_id' => $slot->room_id,
                'duration' => $this->calculateDuration($slot->start_time, $slot->end_time),
            ];
        })->toArray();
    }

    /**
     * Get schedule conflicts for a course
     */
    public function getScheduleConflicts(Course $course, array $scheduleData): array
    {
        $conflicts = [];

        foreach ($scheduleData as $index => $slotData) {
            if (!$this->isValidSlotData($slotData)) {
                continue;
            }

            // Check for room conflicts
            $roomConflicts = $this->checkRoomConflicts(
                $slotData,
                $course->school_id,
                $course->id
            );

            if (!empty($roomConflicts)) {
                $conflicts[$index] = [
                    'type' => 'room_conflict',
                    'conflicts' => $roomConflicts,
                    'slot' => $slotData
                ];
            }

            // Check for instructor conflicts (if you have instructor assignments)
            // $instructorConflicts = $this->checkInstructorConflicts($slotData, $course->id);
        }

        return $conflicts;
    }

    /**
     * Get weekly schedule for a school
     */
    public function getWeeklySchedule(int $schoolId): array
    {
        $days = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
        $schedule = [];

        foreach ($days as $day) {
            $schedule[$day] = ScheduleSlot::with(['course', 'room'])
                ->whereHas('course', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId)
                          ->where('status', 'active');
                })
                ->where('day', $day)
                ->orderBy('start_time')
                ->get();
        }

        return $schedule;
    }

    /**
     * Calculate duration between two times
     */
    public function calculateDuration(string $startTime, string $endTime): string
    {
        try {
            $start = new \DateTime($startTime);
            $end = new \DateTime($endTime);
            $interval = $start->diff($end);

            $hours = $interval->h;
            $minutes = $interval->i;

            $duration = '';
            if ($hours > 0) {
                $duration .= $hours . 'h ';
            }
            if ($minutes > 0) {
                $duration .= $minutes . 'min';
            }

            return trim($duration) ?: '0min';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Check if slot data is valid
     */
    protected function isValidSlotData(array $slotData): bool
    {
        return !empty($slotData['day']) &&
               !empty($slotData['start_time']) &&
               !empty($slotData['end_time']) &&
               $slotData['start_time'] < $slotData['end_time'];
    }

    /**
     * Check for room conflicts
     */
    protected function checkRoomConflicts(array $slotData, int $schoolId, ?int $excludeCourseId = null): array
    {
        if (empty($slotData['room_id'])) {
            return [];
        }

        $conflictQuery = ScheduleSlot::with('course')
            ->where('room_id', $slotData['room_id'])
            ->where('day', $slotData['day'])
            ->whereHas('course', function ($query) use ($schoolId, $excludeCourseId) {
                $query->where('school_id', $schoolId)
                      ->where('status', 'active');

                if ($excludeCourseId) {
                    $query->where('id', '!=', $excludeCourseId);
                }
            })
            ->where(function ($query) use ($slotData) {
                $query->where(function ($q) use ($slotData) {
                    // New slot starts during existing slot
                    $q->where('start_time', '<=', $slotData['start_time'])
                      ->where('end_time', '>', $slotData['start_time']);
                })->orWhere(function ($q) use ($slotData) {
                    // New slot ends during existing slot
                    $q->where('start_time', '<', $slotData['end_time'])
                      ->where('end_time', '>=', $slotData['end_time']);
                })->orWhere(function ($q) use ($slotData) {
                    // New slot completely contains existing slot
                    $q->where('start_time', '>=', $slotData['start_time'])
                      ->where('end_time', '<=', $slotData['end_time']);
                });
            });

        return $conflictQuery->get()->map(function ($slot) {
            return [
                'course_name' => $slot->course->name,
                'time' => $slot->start_time . ' - ' . $slot->end_time,
                'slot_id' => $slot->id
            ];
        })->toArray();
    }

    /**
     * Get room utilization statistics
     */
    public function getRoomUtilization(int $schoolId): array
    {
        $slots = ScheduleSlot::with(['course', 'room'])
            ->whereHas('course', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId)
                      ->where('status', 'active');
            })
            ->whereNotNull('room_id')
            ->get();

        $utilization = [];

        foreach ($slots as $slot) {
            if (!$slot->room) continue;

            $roomId = $slot->room->id;
            $roomName = $slot->room->name;

            if (!isset($utilization[$roomId])) {
                $utilization[$roomId] = [
                    'room_name' => $roomName,
                    'total_hours' => 0,
                    'slots_count' => 0,
                    'courses' => []
                ];
            }

            $duration = $this->calculateDurationInHours($slot->start_time, $slot->end_time);
            $utilization[$roomId]['total_hours'] += $duration;
            $utilization[$roomId]['slots_count']++;
            $utilization[$roomId]['courses'][] = $slot->course->name;
        }

        return $utilization;
    }

    /**
     * Calculate duration in hours (decimal)
     */
    protected function calculateDurationInHours(string $startTime, string $endTime): float
    {
        try {
            $start = new \DateTime($startTime);
            $end = new \DateTime($endTime);
            $interval = $start->diff($end);

            return $interval->h + ($interval->i / 60);
        } catch (\Exception $e) {
            return 0;
        }
    }
}