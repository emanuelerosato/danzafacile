<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $schoolId = $this->route('school');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('schools')->ignore($schoolId)],
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('schools')->ignore($schoolId)],
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'logo_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'active' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome della scuola è obbligatorio.',
            'name.unique' => 'Esiste già una scuola con questo nome.',
            'address.required' => 'L\'indirizzo è obbligatorio.',
            'city.required' => 'La città è obbligatoria.',
            'postal_code.required' => 'Il CAP è obbligatorio.',
            'email.unique' => 'Questa email è già utilizzata da un\'altra scuola.',
            'logo_path.image' => 'Il file deve essere un\'immagine.',
            'logo_path.mimes' => 'Il logo deve essere in formato JPEG, PNG o JPG.',
            'logo_path.max' => 'Il logo non può superare 2MB.',
        ];
    }
}