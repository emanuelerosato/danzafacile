<?php

namespace App\Http\Requests;

use App\Helpers\FileUploadHelper;
use Illuminate\Foundation\Http\FormRequest;

class UploadMediaGalleryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'files' => 'required|array|min:1',
            'files.*' => [
                'required',
                'file',
                'max:10240', // 10MB per file
                'mimes:jpg,jpeg,png,gif,mp4,mov,avi',
                // SECURITY: Magic bytes validation for each file
                function ($attribute, $value, $fail) {
                    if ($value) {
                        // Determine category based on MIME type
                        $mimeType = $value->getMimeType();
                        $category = str_starts_with($mimeType, 'image/') ? 'image' : 'video';

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
            'files.required' => 'Devi caricare almeno un file.',
            'files.*.file' => 'Uno o più file non sono validi.',
            'files.*.mimes' => 'I file devono essere in formato JPG, PNG, GIF, MP4, MOV o AVI.',
            'files.*.max' => 'Ogni file non può superare 10MB.',
        ];
    }
}
