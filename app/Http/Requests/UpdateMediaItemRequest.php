<?php

namespace App\Http\Requests;

use App\Helpers\FileUploadHelper;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaItemRequest extends FormRequest
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
            'gallery_id' => 'required|exists:media_galleries,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:image,video,document',
            'file' => [
                'nullable',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,pdf,mp4,mov,avi',
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
            'gallery_id.required' => 'La galleria è obbligatoria.',
            'gallery_id.exists' => 'La galleria selezionata non esiste.',
            'title.required' => 'Il titolo è obbligatorio.',
            'title.max' => 'Il titolo non può superare 255 caratteri.',
            'description.max' => 'La descrizione non può superare 500 caratteri.',
            'type.required' => 'Il tipo di media è obbligatorio.',
            'type.in' => 'Il tipo di media deve essere image, video o document.',
            'file.file' => 'Devi caricare un file valido.',
            'file.mimes' => 'Il file deve essere in formato JPG, PNG, GIF, PDF, MP4, MOV o AVI.',
            'file.max' => 'Il file non può superare 10MB.',
        ];
    }
}
