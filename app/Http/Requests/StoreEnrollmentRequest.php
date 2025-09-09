<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEnrollmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->isAdmin() || $user->isStudent());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'L\'utente è obbligatorio.',
            'user_id.exists' => 'L\'utente selezionato non esiste.',
            'course_id.required' => 'Il corso è obbligatorio.',
            'course_id.exists' => 'Il corso selezionato non esiste.',
            'enrollment_date.date' => 'La data di iscrizione deve essere una data valida.',
            'notes.max' => 'Le note non possono superare 500 caratteri.',
        ];
    }
}