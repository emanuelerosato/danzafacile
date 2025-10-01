<?php

namespace App\Http\Requests;

use App\Helpers\FileUploadHelper;
use Illuminate\Foundation\Http\FormRequest;

class UploadCourseCoverRequest extends FormRequest
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
            'image' => [
                'nullable',
                'file',
                'max:5120', // 5MB
                'mimes:jpg,jpeg,png,gif',
                // SECURITY: Magic bytes validation
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $validation = FileUploadHelper::validateFile($value, 'image', 5);

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
            'image.file' => 'Devi caricare un file valido.',
            'image.mimes' => 'L\'immagine deve essere in formato JPG, PNG o GIF.',
            'image.max' => 'L\'immagine non può superare 5MB.',
        ];
    }
}
