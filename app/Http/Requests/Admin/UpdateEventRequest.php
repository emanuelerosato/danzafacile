<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\FileUploadHelper;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determina se l'utente è autorizzato a fare questa richiesta.
     */
    public function authorize(): bool
    {
        // L'autorizzazione viene gestita dalla EventPolicy nel controller
        // Qui verifichiamo solo che l'utente abbia una scuola associata
        return auth()->check() &&
               (auth()->user()->isSuperAdmin() || auth()->user()->school_id !== null);
    }

    /**
     * Regole di validazione per l'aggiornamento di un evento.
     */
    public function rules(): array
    {
        return [
            // Campi base
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string|max:500',
            'type' => 'required|in:saggio,workshop,competizione,seminario,altro',

            // Date - Per update, start_date può essere nel passato (per eventi già iniziati)
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_deadline' => 'nullable|date|before:start_date',

            // Location
            'location' => 'nullable|string|max:255',

            // Partecipanti
            'max_participants' => 'nullable|integer|min:1',

            // Prezzi - Dual pricing per studenti e guest
            'price_students' => 'nullable|numeric|min:0|max:999999.99',
            'price_guests' => 'nullable|numeric|min:0|max:999999.99',
            'requires_payment' => 'boolean',
            'payment_method' => 'nullable|in:cash,card,bank_transfer,paypal',

            // Registrazione
            'requires_registration' => 'boolean',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',

            // Links esterni
            'external_link' => 'nullable|url|max:500',
            'social_link' => 'nullable|url|max:500',

            // Immagine con validazione avanzata
            'image' => [
                'nullable',
                'file',
                'max:5120', // 5MB
                'mimes:jpg,jpeg,png,gif,webp',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $validation = FileUploadHelper::validateFile($value, 'image', 5);
                        if (!$validation['valid']) {
                            $fail(implode(' ', $validation['errors']));
                        }
                    }
                }
            ],

            // Visibilità e stato
            'is_public' => 'boolean',
            'active' => 'boolean',

            // Landing page customization
            'landing_description' => 'nullable|string|max:2000',
            'landing_cta_text' => 'nullable|string|max:100',

            // Check-in QR
            'qr_checkin_enabled' => 'boolean',

            // Info aggiuntive
            'additional_info' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Messaggi di errore personalizzati in italiano.
     */
    public function messages(): array
    {
        return [
            // Nome
            'name.required' => 'Il nome dell\'evento è obbligatorio.',
            'name.string' => 'Il nome dell\'evento deve essere una stringa.',
            'name.max' => 'Il nome dell\'evento non può superare 255 caratteri.',

            // Descrizioni
            'short_description.max' => 'La descrizione breve non può superare 500 caratteri.',
            'landing_description.max' => 'La descrizione della landing page non può superare 2000 caratteri.',
            'landing_cta_text.max' => 'Il testo del pulsante CTA non può superare 100 caratteri.',

            // Tipo
            'type.required' => 'Il tipo di evento è obbligatorio.',
            'type.in' => 'Il tipo di evento deve essere: saggio, workshop, competizione, seminario o altro.',

            // Date
            'start_date.required' => 'La data di inizio è obbligatoria.',
            'start_date.date' => 'La data di inizio deve essere una data valida.',
            'end_date.required' => 'La data di fine è obbligatoria.',
            'end_date.date' => 'La data di fine deve essere una data valida.',
            'end_date.after_or_equal' => 'La data di fine deve essere uguale o successiva alla data di inizio.',
            'registration_deadline.date' => 'La scadenza registrazioni deve essere una data valida.',
            'registration_deadline.before' => 'La scadenza registrazioni deve essere prima della data di inizio evento.',

            // Location
            'location.string' => 'La location deve essere una stringa.',
            'location.max' => 'La location non può superare 255 caratteri.',

            // Partecipanti
            'max_participants.integer' => 'Il numero massimo di partecipanti deve essere un numero intero.',
            'max_participants.min' => 'Il numero massimo di partecipanti deve essere almeno 1.',

            // Prezzi
            'price_students.numeric' => 'Il prezzo per studenti deve essere un numero.',
            'price_students.min' => 'Il prezzo per studenti non può essere negativo.',
            'price_students.max' => 'Il prezzo per studenti non può superare 999999.99.',
            'price_guests.numeric' => 'Il prezzo per ospiti deve essere un numero.',
            'price_guests.min' => 'Il prezzo per ospiti non può essere negativo.',
            'price_guests.max' => 'Il prezzo per ospiti non può superare 999999.99.',
            'payment_method.in' => 'Il metodo di pagamento deve essere: cash, card, bank_transfer o paypal.',

            // Requirements
            'requirements.array' => 'I requisiti devono essere un array.',
            'requirements.*.string' => 'Ogni requisito deve essere una stringa.',
            'requirements.*.max' => 'Ogni requisito non può superare 255 caratteri.',

            // Links
            'external_link.url' => 'Il link esterno deve essere un URL valido.',
            'external_link.max' => 'Il link esterno non può superare 500 caratteri.',
            'social_link.url' => 'Il link social deve essere un URL valido.',
            'social_link.max' => 'Il link social non può superare 500 caratteri.',

            // Immagine
            'image.file' => 'L\'immagine deve essere un file valido.',
            'image.max' => 'L\'immagine non può superare 5MB.',
            'image.mimes' => 'L\'immagine deve essere di tipo: jpg, jpeg, png, gif o webp.',

            // Info aggiuntive
            'additional_info.max' => 'Le informazioni aggiuntive non possono superare 1000 caratteri.',
        ];
    }

    /**
     * Prepara i dati per la validazione.
     * Imposta valori di default per i campi booleani.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_registration' => $this->boolean('requires_registration', false),
            'is_public' => $this->boolean('is_public', true),
            'active' => $this->boolean('active', true),
            'requires_payment' => $this->boolean('requires_payment', false),
            'qr_checkin_enabled' => $this->boolean('qr_checkin_enabled', false),
        ]);
    }

    /**
     * Ottieni i dati validati con prezzi di default impostati.
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        // Imposta prezzi di default se non forniti
        $validated['price_students'] = $validated['price_students'] ?? 0.00;
        $validated['price_guests'] = $validated['price_guests'] ?? 0.00;

        return $validated;
    }
}
