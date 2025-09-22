<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'difficulty_level' => $this->difficulty_level,
            'duration_weeks' => $this->duration_weeks,
            'max_students' => $this->max_students,
            'price' => $this->price,
            'status' => $this->status,
            'equipment' => $this->equipment ?? [],
            'objectives' => $this->objectives ?? [],

            // Schedule information (using JSON field)
            'schedule_slots' => $this->schedule_data ?? [],

            // Media information
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'type' => $media->type,
                        'url' => $media->url,
                        'thumbnail_url' => $media->thumbnail_url,
                    ];
                });
            }),

            // Enrollment information
            'enrollments_count' => $this->whenCounted('enrollments'),
            'available_spots' => $this->max_students ?
                max(0, $this->max_students - ($this->enrollments_count ?? 0)) : null,

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Additional metadata
            'meta' => [
                'is_full' => $this->max_students &&
                    ($this->enrollments_count ?? 0) >= $this->max_students,
                'can_enroll' => $this->status === 'active' &&
                    (!$this->max_students || ($this->enrollments_count ?? 0) < $this->max_students),
                'total_duration_hours' => $this->calculateTotalDurationHours(),
            ],
        ];
    }

    /**
     * Calculate total duration hours from schedule slots
     */
    protected function calculateTotalDurationHours(): ?float
    {
        $scheduleData = $this->schedule_data;
        if (!$scheduleData || empty($scheduleData)) {
            return null;
        }

        $totalMinutes = 0;
        foreach ($scheduleData as $slot) {
            try {
                if (empty($slot['start_time']) || empty($slot['end_time'])) {
                    continue;
                }
                $start = new \DateTime($slot['start_time']);
                $end = new \DateTime($slot['end_time']);
                $diff = $start->diff($end);
                $totalMinutes += ($diff->h * 60) + $diff->i;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $totalMinutes > 0 ? round($totalMinutes / 60, 2) : null;
    }
}
