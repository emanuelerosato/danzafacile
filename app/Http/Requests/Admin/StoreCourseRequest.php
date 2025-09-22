<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'difficulty_level' => 'nullable|in:Principiante,Intermedio,Avanzato,Professionale',
            'duration_weeks' => 'nullable|integer|min:1|max:52',
            'max_students' => 'nullable|integer|min:1|max:50',
            'price' => 'nullable|numeric|min:0|max:9999.99',
            'status' => 'required|in:draft,active,inactive,completed',

            // Schedule validation
            'schedule_slots' => 'nullable|array|max:20',
            'schedule_slots.*.day' => 'required_with:schedule_slots.*|string|in:Lunedì,Martedì,Mercoledì,Giovedì,Venerdì,Sabato,Domenica',
            'schedule_slots.*.start_time' => 'required_with:schedule_slots.*|date_format:H:i',
            'schedule_slots.*.end_time' => 'required_with:schedule_slots.*|date_format:H:i|after:schedule_slots.*.start_time',
            'schedule_slots.*.room_id' => 'nullable|integer|exists:rooms,id',

            // Equipment and objectives
            'equipment' => 'nullable|array|max:20',
            'equipment.*' => 'string|max:255',
            'objectives' => 'nullable|array|max:20',
            'objectives.*' => 'string|max:255',

            // Media uploads
            'media' => 'nullable|array|max:10',
            'media.*' => 'file|mimes:jpeg,jpg,png,gif,mp4,mov|max:20480', // 20MB max
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del corso è obbligatorio.',
            'name.max' => 'Il nome del corso non può superare i 255 caratteri.',
            'type.required' => 'Il tipo di corso è obbligatorio.',
            'difficulty_level.in' => 'Il livello di difficoltà selezionato non è valido.',
            'duration_weeks.integer' => 'La durata deve essere un numero intero.',
            'duration_weeks.min' => 'La durata deve essere almeno 1 settimana.',
            'duration_weeks.max' => 'La durata non può superare le 52 settimane.',
            'max_students.integer' => 'Il numero massimo di studenti deve essere un numero intero.',
            'max_students.min' => 'Deve esserci almeno 1 posto disponibile.',
            'max_students.max' => 'Il numero massimo di studenti non può superare 50.',
            'price.numeric' => 'Il prezzo deve essere un numero valido.',
            'price.min' => 'Il prezzo non può essere negativo.',
            'status.required' => 'Lo stato del corso è obbligatorio.',
            'status.in' => 'Lo stato selezionato non è valido.',

            // Schedule messages
            'schedule_slots.max' => 'Non puoi aggiungere più di 20 orari.',
            'schedule_slots.*.day.required_with' => 'Il giorno è obbligatorio per ogni orario.',
            'schedule_slots.*.day.in' => 'Il giorno selezionato non è valido.',
            'schedule_slots.*.start_time.required_with' => 'L\'ora di inizio è obbligatoria.',
            'schedule_slots.*.start_time.date_format' => 'L\'ora di inizio deve essere nel formato HH:MM.',
            'schedule_slots.*.end_time.required_with' => 'L\'ora di fine è obbligatoria.',
            'schedule_slots.*.end_time.date_format' => 'L\'ora di fine deve essere nel formato HH:MM.',
            'schedule_slots.*.end_time.after' => 'L\'ora di fine deve essere successiva all\'ora di inizio.',
            'schedule_slots.*.room_id.exists' => 'La sala selezionata non è valida.',

            // Equipment and objectives messages
            'equipment.max' => 'Non puoi aggiungere più di 20 attrezzature.',
            'equipment.*.max' => 'Il nome dell\'attrezzatura non può superare i 255 caratteri.',
            'objectives.max' => 'Non puoi aggiungere più di 20 obiettivi.',
            'objectives.*.max' => 'Il testo dell\'obiettivo non può superare i 255 caratteri.',

            // Media messages
            'media.max' => 'Non puoi caricare più di 10 file.',
            'media.*.mimes' => 'I file devono essere immagini (jpeg, jpg, png, gif) o video (mp4, mov).',
            'media.*.max' => 'Ogni file non può superare i 20MB.',
        ];
    }

    /**
     * Get custom attribute names for validation errors
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome del corso',
            'type' => 'tipo di corso',
            'description' => 'descrizione',
            'difficulty_level' => 'livello di difficoltà',
            'duration_weeks' => 'durata in settimane',
            'max_students' => 'numero massimo di studenti',
            'price' => 'prezzo',
            'status' => 'stato del corso',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Clean up equipment and objectives arrays
        if ($this->has('equipment')) {
            $equipment = array_filter($this->equipment, fn($item) => !empty(trim($item)));
            $this->merge(['equipment' => array_values($equipment)]);
        }

        if ($this->has('objectives')) {
            $objectives = array_filter($this->objectives, fn($item) => !empty(trim($item)));
            $this->merge(['objectives' => array_values($objectives)]);
        }

        // Clean up schedule slots
        if ($this->has('schedule_slots')) {
            $scheduleSlots = array_filter($this->schedule_slots, function($slot) {
                return !empty($slot['day']) && !empty($slot['start_time']) && !empty($slot['end_time']);
            });
            $this->merge(['schedule_slots' => array_values($scheduleSlots)]);
        }
    }
}
