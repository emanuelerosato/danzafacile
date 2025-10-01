<?php

namespace App\Http\Requests;

use App\Helpers\FileUploadHelper;
use Illuminate\Foundation\Http\FormRequest;

class UploadMediaItemRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,pdf,mp4,mov',
                // SECURITY: Magic bytes validation
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $mimeType = $value->getMimeType();

                        // Determine category
                        if (str_starts_with($mimeType, 'image/')) {
                            $category = 'image';
                        } elseif ($mimeType === 'application/pdf') {
                            $category = 'document';
                        } else {
                            $category = 'video';
                        }

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
            'file.required' => 'Il file è obbligatorio.',
            'file.file' => 'Devi caricare un file valido.',
            'file.mimes' => 'Il file deve essere in formato JPG, PNG, GIF, PDF, MP4 o MOV.',
            'file.max' => 'Il file non può superare 10MB.',
        ];
    }
}
