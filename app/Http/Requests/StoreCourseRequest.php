<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'schedule_slots' => 'nullable|array',
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
}