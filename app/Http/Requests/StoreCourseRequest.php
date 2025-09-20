<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->isAdmin());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'dance_type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'level' => 'required|in:Principiante,Intermedio,Avanzato,Professionale,beginner,intermediate,advanced,professional,principiante,base,intermedio,avanzato,professionale',
            'min_age' => 'nullable|integer|min:1|max:100',
            'max_age' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|string',
            'prerequisites' => 'nullable|string',
            'equipment' => 'nullable|array',
            'objectives' => 'nullable|array',
            'notes' => 'nullable|string',
            'instructor_id' => 'nullable|integer|exists:users,id',
            'max_students' => 'required|integer|min:1',
            'monthly_price' => 'required|numeric|min:0',
            'enrollment_fee' => 'nullable|numeric|min:0',
            'single_lesson_price' => 'nullable|numeric|min:0',
            'trial_price' => 'nullable|numeric|min:0',
            'price_application' => 'nullable|string',
            'price_effective_date' => 'required|date|after_or_equal:today',
            'schedule' => 'nullable|string|max:500',
            'schedule_slots' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    if (!$value || !is_array($value)) return;

                    $instructorId = $this->input('instructor_id');
                    $startDate = $this->input('start_date');
                    $endDate = $this->input('end_date');

                    if (!$instructorId || !$startDate) return;

                    // Check for conflicts with other courses
                    $this->validateScheduleConflicts($value, null, $instructorId, $startDate, $endDate, $fail);
                }
            ],
            'schedule_slots.*.day' => 'required_with:schedule_slots|string|in:Lunedì,Martedì,Mercoledì,Giovedì,Venerdì,Sabato,Domenica',
            'schedule_slots.*.start_time' => 'required_with:schedule_slots|date_format:H:i',
            'schedule_slots.*.end_time' => 'required_with:schedule_slots|date_format:H:i|after:schedule_slots.*.start_time',
            'schedule_slots.*.location' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'duration_weeks' => 'nullable|integer|min:1|max:52',
            'active' => 'boolean'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'school_id.required' => 'La scuola è obbligatoria.',
            'school_id.exists' => 'La scuola selezionata non esiste.',
            'name.required' => 'Il nome del corso è obbligatorio.',
            'max_students.required' => 'Il numero massimo di studenti è obbligatorio.',
            'max_students.min' => 'Il corso deve avere almeno 1 posto disponibile.',
            'price.required' => 'Il prezzo è obbligatorio.',
            'price.min' => 'Il prezzo non può essere negativo.',
            'start_date.required' => 'La data di inizio è obbligatoria.',
            'start_date.after_or_equal' => 'La data di inizio non può essere nel passato.',
            'end_date.required' => 'La data di fine è obbligatoria.',
            'end_date.after' => 'La data di fine deve essere successiva alla data di inizio.',
            'schedule_days.required' => 'Seleziona almeno un giorno della settimana.',
            'start_time.required' => 'L\'orario di inizio è obbligatorio.',
            'end_time.required' => 'L\'orario di fine è obbligatorio.',
            'end_time.after' => 'L\'orario di fine deve essere successivo all\'orario di inizio.',
            'level.required' => 'Il livello è obbligatorio.',
            'age_max.gte' => 'L\'età massima deve essere maggiore o uguale all\'età minima.',
        ];
    }

    /**
     * Validate schedule conflicts for instructor and location
     */
    private function validateScheduleConflicts($scheduleSlots, $course, $instructorId, $startDate, $endDate, $fail)
    {
        $user = auth()->user();
        if (!$user || !$user->school_id) return;

        foreach ($scheduleSlots as $index => $slot) {
            if (empty($slot['day']) || empty($slot['start_time']) || empty($slot['end_time'])) {
                continue;
            }

            // Find other courses with same instructor that have overlapping schedules
            $conflictingCourses = \App\Models\Course::where('school_id', $user->school_id)
                ->where('instructor_id', $instructorId)
                ->where('active', true)
                ->when($course, function($q) use ($course) {
                    return $q->where('id', '!=', $course->id);
                })
                ->whereNotNull('schedule')
                ->get();

            foreach ($conflictingCourses as $conflictCourse) {
                $conflictSchedule = $conflictCourse->schedule_data;
                if (!$conflictSchedule || !is_array($conflictSchedule)) continue;

                // Check if course dates overlap
                if (!$this->courseDatesOverlap($startDate, $endDate, $conflictCourse->start_date, $conflictCourse->end_date)) {
                    continue;
                }

                foreach ($conflictSchedule as $conflictSlot) {
                    if (empty($conflictSlot['day']) || empty($conflictSlot['start_time']) || empty($conflictSlot['end_time'])) {
                        continue;
                    }

                    // Check if same day and overlapping time
                    if ($slot['day'] === $conflictSlot['day'] &&
                        $this->timesOverlap($slot['start_time'], $slot['end_time'], $conflictSlot['start_time'], $conflictSlot['end_time'])) {

                        $fail("Conflitto di orario per l'istruttore: {$slot['day']} dalle {$slot['start_time']} alle {$slot['end_time']} si sovrappone con il corso \"{$conflictCourse->name}\".");
                        return;
                    }

                    // Check location conflicts if both slots have locations
                    if (!empty($slot['location']) && !empty($conflictSlot['location']) &&
                        $slot['location'] === $conflictSlot['location'] &&
                        $slot['day'] === $conflictSlot['day'] &&
                        $this->timesOverlap($slot['start_time'], $slot['end_time'], $conflictSlot['start_time'], $conflictSlot['end_time'])) {

                        $fail("Conflitto di location \"{$slot['location']}\": {$slot['day']} dalle {$slot['start_time']} alle {$slot['end_time']} si sovrappone con il corso \"{$conflictCourse->name}\".");
                        return;
                    }
                }
            }
        }
    }

    /**
     * Check if two date ranges overlap
     */
    private function courseDatesOverlap($start1, $end1, $start2, $end2)
    {
        $start1 = Carbon::parse($start1);
        $end1 = $end1 ? Carbon::parse($end1) : null;
        $start2 = Carbon::parse($start2);
        $end2 = $end2 ? Carbon::parse($end2) : null;

        // If either course has no end date, assume they could overlap
        if (!$end1 || !$end2) {
            return true;
        }

        // Check for overlap: start1 <= end2 && start2 <= end1
        return $start1->lte($end2) && $start2->lte($end1);
    }

    /**
     * Check if two time ranges overlap
     */
    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        $start1 = Carbon::createFromFormat('H:i', $start1);
        $end1 = Carbon::createFromFormat('H:i', $end1);
        $start2 = Carbon::createFromFormat('H:i', $start2);
        $end2 = Carbon::createFromFormat('H:i', $end2);

        // Check for overlap: start1 < end2 && start2 < end1
        return $start1->lt($end2) && $start2->lt($end1);
    }
}