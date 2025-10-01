<?php

namespace App\Http\Requests;

use App\Helpers\FileUploadHelper;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketResponseRequest extends FormRequest
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
            'message' => 'required|string|min:10|max:5000',
            'is_internal' => 'boolean',
            'attachments' => 'nullable|array',
            'attachments.*' => [
                'nullable',
                'file',
                'max:5120', // 5MB
                'mimes:jpg,jpeg,png,gif,pdf',
                // SECURITY: Magic bytes validation
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $mimeType = $value->getMimeType();
                        $category = str_starts_with($mimeType, 'image/') ? 'image' : 'document';

                        $validation = FileUploadHelper::validateFile($value, $category, 5);

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
            'message.required' => 'Il messaggio è obbligatorio.',
            'message.min' => 'Il messaggio deve essere di almeno 10 caratteri.',
            'message.max' => 'Il messaggio non può superare i 5000 caratteri.',
            'attachments.*.file' => 'Devi caricare file validi.',
            'attachments.*.mimes' => 'Solo file JPG, PNG, GIF, PDF sono permessi.',
            'attachments.*.max' => 'Ogni file non può superare i 5MB.',
        ];
    }
}
