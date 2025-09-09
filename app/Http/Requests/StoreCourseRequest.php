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
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'instructor_id' => 'nullable|exists:users,id',
            'max_students' => 'required|integer|min:1|max:100',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'schedule_days' => 'required|array|min:1',
            'schedule_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'level' => 'required|in:beginner,intermediate,advanced',
            'active' => 'boolean',
            'age_min' => 'nullable|integer|min:3|max:100',
            'age_max' => 'nullable|integer|min:3|max:100|gte:age_min',
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