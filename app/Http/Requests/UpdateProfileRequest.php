<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
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
        $user = auth()->user();

        return [
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_password' => 'nullable|string|current_password',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome è obbligatorio.',
            'email.required' => 'L\'email è obbligatoria.',
            'email.unique' => 'Questa email è già utilizzata.',
            'date_of_birth.before' => 'La data di nascita deve essere nel passato.',
            'profile_image.image' => 'Il file deve essere un\'immagine.',
            'profile_image.mimes' => 'L\'immagine deve essere in formato JPEG, PNG o JPG.',
            'profile_image.max' => 'L\'immagine non può superare 2MB.',
            'current_password.current_password' => 'La password corrente non è corretta.',
            'password.min' => 'La nuova password deve avere almeno 8 caratteri.',
            'password.confirmed' => 'La conferma della password non corrisponde.',
        ];
    }
}