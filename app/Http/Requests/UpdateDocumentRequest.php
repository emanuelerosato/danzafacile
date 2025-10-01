<?php

namespace App\Http\Requests;

use App\Helpers\FileUploadHelper;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|in:general,medical,contract,identification,other',
            'file' => [
                'nullable',
                'file',
                'max:10240', // 10MB
                'mimes:pdf,jpg,jpeg,png,gif,doc,docx,txt',
                // SECURITY: Magic bytes validation
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $mimeType = $value->getMimeType();
                        $category = str_starts_with($mimeType, 'image/') ? 'image' : 'document';

                        $validation = FileUploadHelper::validateFile($value, $category, 10);

                        if (!$validation['valid']) {
                            $fail(implode(' ', $validation['errors']));
                            return;
                        }

                        $filename = $value->getClientOriginalName();
                        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
                            $fail('Il nome del file non è valido.');
                        }
                    }
                },
            ],
            'is_public' => 'boolean',
            'requires_approval' => 'boolean',
            'expires_at' => 'nullable|date|after:now'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Il titolo del documento è obbligatorio.',
            'title.max' => 'Il titolo non può superare 255 caratteri.',
            'description.max' => 'La descrizione non può superare 1000 caratteri.',
            'category.required' => 'La categoria è obbligatoria.',
            'category.in' => 'Categoria non valida.',
            'file.file' => 'Devi caricare un file valido.',
            'file.mimes' => 'Il file deve essere in formato PDF, JPG, PNG, GIF, DOC, DOCX o TXT.',
            'file.max' => 'Il file non può superare 10MB.',
            'expires_at.after' => 'La data di scadenza deve essere futura.',
        ];
    }
}
