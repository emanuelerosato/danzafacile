<?php

namespace App\Http\Requests;

use App\Helpers\FileUploadHelper;
use Illuminate\Foundation\Http\FormRequest;

class UploadSchoolLogoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'logo' => [
                'nullable',
                'file',
                'max:2048', // 2MB for logo
                'mimes:jpg,jpeg,png',
                // SECURITY: Magic bytes validation
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $validation = FileUploadHelper::validateFile($value, 'image', 2);

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
            'logo.file' => 'Devi caricare un file valido.',
            'logo.mimes' => 'Il logo deve essere in formato JPG o PNG.',
            'logo.max' => 'Il logo non può superare 2MB.',
        ];
    }
}
