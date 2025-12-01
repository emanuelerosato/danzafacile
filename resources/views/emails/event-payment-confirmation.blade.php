@extends('emails.layout')

@section('title', 'Pagamento Ricevuto')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-block; width: 80px; height: 80px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <span style="font-size: 40px;">💳</span>
        </div>
        <h2 style="color: #1f2937; margin: 0; font-size: 28px; font-weight: 700;">Pagamento Ricevuto!</h2>
    </div>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        Ciao <strong>{{ $user->name }}</strong>,
    </p>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 30px;">
        Il tuo pagamento per l'evento <strong>{{ $event->name }}</strong> è stato ricevuto ed elaborato con successo.
        La tua registrazione è ora <strong style="color: #10b981;">confermata</strong>!
    </p>

    <div class="success" style="background: #d1fae5; padding: 25px; border-left: 4px solid #10b981; margin: 30px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 20px 0; font-size: 20px; font-weight: 600; color: #065f46; text-align: center;">
            🧾 Ricevuta Pagamento
        </h3>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
            <tr style="border-bottom: 1px solid #a7f3d0;">
                <td style="padding: 12px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">ID Transazione:</strong>
                </td>
                <td style="padding: 12px 0; font-size: 15px; color: #1f2937; font-family: monospace;">
                    {{ $payment->transaction_id }}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #a7f3d0;">
                <td style="padding: 12px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Data Pagamento:</strong>
                </td>
                <td style="padding: 12px 0; font-size: 15px; color: #1f2937;">
                    {{ $payment->paid_at->format('d/m/Y H:i') }}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #a7f3d0;">
                <td style="padding: 12px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Metodo:</strong>
                </td>
                <td style="padding: 12px 0; font-size: 15px; color: #1f2937;">
                    @if($payment->payment_method === 'stripe')
                        Carta di Credito/Debito (Stripe)
                    @elseif($payment->payment_method === 'paypal')
                        PayPal
                    @elseif($payment->payment_method === 'bank_transfer')
                        Bonifico Bancario
                    @else
                        {{ ucfirst($payment->payment_method) }}
                    @endif
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #a7f3d0;">
                <td style="padding: 12px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Descrizione:</strong>
                </td>
                <td style="padding: 12px 0; font-size: 15px; color: #1f2937;">
                    Iscrizione Evento: {{ $event->name }}
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 0; font-size: 18px; color: #065f46;">
                    <strong>Importo Pagato:</strong>
                </td>
                <td style="padding: 12px 0; font-size: 20px; color: #065f46; font-weight: 700;">
                    €{{ number_format($payment->amount, 2, ',', '.') }}
                </td>
            </tr>
        </table>

        <div style="text-align: center; padding: 15px; background-color: #ecfdf5; border-radius: 6px; margin-top: 15px;">
            <p style="margin: 0; font-size: 14px; color: #065f46;">
                ✓ Stato: <strong style="font-size: 16px;">PAGATO</strong>
            </p>
        </div>
    </div>

    <div style="background-color: #fdf2f8; padding: 20px; border-left: 4px solid #f43f5e; margin: 25px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #9333ea;">
            📅 Dettagli Evento
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 6px 0; font-size: 15px; color: #6b7280; width: 35%;">
                    <strong style="color: #1f2937;">Evento:</strong>
                </td>
                <td style="padding: 6px 0; font-size: 15px; color: #1f2937;">
                    {{ $event->name }}
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Data:</strong>
                </td>
                <td style="padding: 6px 0; font-size: 15px; color: #1f2937;">
                    {{ $event->start_date->format('d/m/Y') }}
                    @if($event->start_date->format('H:i') !== '00:00')
                        alle {{ $event->start_date->format('H:i') }}
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Luogo:</strong>
                </td>
                <td style="padding: 6px 0; font-size: 15px; color: #1f2937;">
                    {{ $event->location }}
                </td>
            </tr>
            <tr>
                <td style="padding: 6px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Registrazione:</strong>
                </td>
                <td style="padding: 6px 0; font-size: 15px; color: #1f2937;">
                    #{{ $registration->id }}
                </td>
            </tr>
        </table>
    </div>

    <h3 style="margin: 30px 0 15px 0; font-size: 20px; font-weight: 600; color: #1f2937;">
        📱 Accedi al tuo QR Code
    </h3>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 25px;">
        Ora puoi accedere al tuo QR code personale che ti servirà per il check-in all'evento.
        Salvalo sul tuo smartphone o stampalo per averlo sempre con te!
    </p>

    <div style="text-align: center; margin: 40px 0;">
        <a href="{{ route('guest.dashboard', ['token' => $user->guest_token]) }}" class="button" style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; margin-bottom: 15px;">
            📱 Scarica QR Code
        </a>
        <br>
        <a href="{{ route('events.show', $event->id) }}" style="color: #9333ea; text-decoration: none; font-size: 14px;">
            📅 Aggiungi al Calendario
        </a>
    </div>

    <div style="background-color: #f0f9ff; padding: 20px; border-left: 4px solid #3b82f6; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #1e40af; line-height: 1.6;">
            <strong>💡 Suggerimento:</strong> Riceverai un promemoria automatico 3 giorni prima dell'evento
            con tutti i dettagli e le informazioni utili per la tua partecipazione.
        </p>
    </div>

    <hr style="border: 0; height: 1px; background-color: #e5e7eb; margin: 30px 0;">

    <p style="font-size: 14px; color: #6b7280; text-align: center;">
        Hai bisogno di assistenza? Contattaci:<br>
        📧 <a href="mailto:{{ config('mail.from.address') }}" style="color: #f43f5e; text-decoration: none;">{{ config('mail.from.address') }}</a>
    </p>

    <p style="font-size: 13px; color: #9ca3af; text-align: center; margin-top: 20px;">
        Conserva questa email come ricevuta del tuo pagamento.
    </p>
@endsection
