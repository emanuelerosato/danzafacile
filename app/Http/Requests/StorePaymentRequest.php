<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSuperAdmin() || $user->isAdmin());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,paypal',
            'transaction_id' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:payment_date',
            'status' => 'required|in:pending,completed,failed,refunded',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'L\'utente è obbligatorio.',
            'user_id.exists' => 'L\'utente selezionato non esiste.',
            'course_id.exists' => 'Il corso selezionato non esiste.',
            'amount.required' => 'L\'importo è obbligatorio.',
            'amount.min' => 'L\'importo deve essere maggiore di zero.',
            'payment_method.required' => 'Il metodo di pagamento è obbligatorio.',
            'payment_method.in' => 'Metodo di pagamento non valido.',
            'payment_date.required' => 'La data di pagamento è obbligatoria.',
            'due_date.after_or_equal' => 'La data di scadenza deve essere successiva o uguale alla data di pagamento.',
            'status.required' => 'Lo stato del pagamento è obbligatorio.',
            'status.in' => 'Stato del pagamento non valido.',
            'notes.max' => 'Le note non possono superare 500 caratteri.',
        ];
    }
}