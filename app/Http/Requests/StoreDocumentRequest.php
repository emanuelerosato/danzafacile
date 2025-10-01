<?php

namespace App\Http\Requests;

use App\Helpers\FileUploadHelper;
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
        $categories = array_keys(\App\Models\Document::getAvailableCategories());

        return [
            'user_id' => [
                'sometimes',
                'exists:users,id',
                // Additional validation for admin context
                function ($attribute, $value, $fail) {
                    if ($value && auth()->user()?->isAdmin()) {
                        $user = \App\Models\User::find($value);
                        if (!$user || $user->school_id !== auth()->user()->school_id || $user->role !== 'user') {
                            $fail('Lo studente selezionato non è valido per la tua scuola.');
                        }
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', $categories),
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB consistent with controllers
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                // SECURITY: Advanced file validation with magic bytes checking
                function ($attribute, $value, $fail) {
                    if ($value) {
                        // Determine category based on MIME type
                        $mimeType = $value->getMimeType();
                        $category = str_starts_with($mimeType, 'image/') ? 'images' : 'documents';

                        // Validate with FileUploadHelper (magic bytes + MIME check)
                        $validation = FileUploadHelper::validateFile($value, $category, 10);

                        if (!$validation['valid']) {
                            $fail(implode(' ', $validation['errors']));
                            return;
                        }

                        // Prevent path traversal in filename
                        $filename = $value->getClientOriginalName();
                        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
                            $fail('Il nome del file non è valido.');
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'L\'utente selezionato non esiste.',
            'name.required' => 'Il nome del documento è obbligatorio.',
            'name.max' => 'Il nome non può superare 255 caratteri.',
            'category.required' => 'La categoria è obbligatoria.',
            'category.in' => 'Categoria non valida.',
            'file.required' => 'Il file è obbligatorio.',
            'file.mimes' => 'Il file deve essere in formato PDF, JPG, PNG, DOC o DOCX.',
            'file.max' => 'Il file non può superare 10MB.',
        ];
    }
}