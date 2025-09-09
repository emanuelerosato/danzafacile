<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:medical_certificate,identity_document,insurance,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
            'expiry_date' => 'nullable|date|after:today',
            'is_required' => 'boolean',
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
            'type.required' => 'Il tipo di documento è obbligatorio.',
            'type.in' => 'Tipo di documento non valido.',
            'title.required' => 'Il titolo è obbligatorio.',
            'title.max' => 'Il titolo non può superare 255 caratteri.',
            'description.max' => 'La descrizione non può superare 500 caratteri.',
            'file.required' => 'Il file è obbligatorio.',
            'file.mimes' => 'Il file deve essere in formato PDF, DOC, DOCX, JPG, JPEG o PNG.',
            'file.max' => 'Il file non può superare 5MB.',
            'expiry_date.after' => 'La data di scadenza deve essere futura.',
        ];
    }
}