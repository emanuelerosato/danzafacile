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
        $categories = array_keys(\App\Models\Document::getAvailableCategories());

        return [
            'name' => 'required|string|max:255',
            'category' => 'required|in:' . implode(',', $categories),
            'file' => [
                'nullable',
                'file',
                'max:10240', // 10MB
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                // SECURITY: Magic bytes validation
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $mimeType = $value->getMimeType();
                        $category = str_starts_with($mimeType, 'image/') ? 'images' : 'documents';

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
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del documento è obbligatorio.',
            'name.max' => 'Il nome non può superare 255 caratteri.',
            'category.required' => 'La categoria è obbligatoria.',
            'category.in' => 'Categoria non valida.',
            'file.file' => 'Devi caricare un file valido.',
            'file.mimes' => 'Il file deve essere in formato PDF, JPG, PNG, DOC o DOCX.',
            'file.max' => 'Il file non può superare 10MB.',
        ];
    }
}
