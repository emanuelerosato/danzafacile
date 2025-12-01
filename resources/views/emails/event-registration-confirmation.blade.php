@extends('emails.layout')

@section('title', 'Registrazione Confermata')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-block; width: 80px; height: 80px; background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <span style="font-size: 40px;">✅</span>
        </div>
        <h2 style="color: #1f2937; margin: 0; font-size: 28px; font-weight: 700;">Registrazione Confermata!</h2>
    </div>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        Ciao <strong>{{ $user->name }}</strong>,
    </p>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 30px;">
        La tua registrazione all'evento <strong>{{ $event->name }}</strong> è stata confermata con successo!
        Siamo entusiasti di averti con noi.
    </p>

    <div class="success" style="background: #d1fae5; padding: 20px; border-left: 4px solid #10b981; margin: 30px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #065f46;">
            📋 Dettagli Registrazione
        </h3>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Codice Registrazione:</strong>
                </td>
                <td style="padding: 8px 0; font-size: 15px; color: #1f2937; font-weight: 600;">
                    #{{ $registration->id }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Evento:</strong>
                </td>
                <td style="padding: 8px 0; font-size: 15px; color: #1f2937;">
                    {{ $event->name }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Data:</strong>
                </td>
                <td style="padding: 8px 0; font-size: 15px; color: #1f2937;">
                    {{ $event->start_date->format('d/m/Y') }}
                    @if($event->start_date->format('H:i') !== '00:00')
                        alle {{ $event->start_date->format('H:i') }}
                    @endif
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Luogo:</strong>
                </td>
                <td style="padding: 8px 0; font-size: 15px; color: #1f2937;">
                    {{ $event->location }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Stato:</strong>
                </td>
                <td style="padding: 8px 0; font-size: 15px; color: #1f2937;">
                    @if($registration->status === 'confirmed')
                        <span style="color: #10b981; font-weight: 600;">✓ Confermato</span>
                    @elseif($registration->status === 'pending_payment')
                        <span style="color: #f59e0b; font-weight: 600;">⏳ In Attesa di Pagamento</span>
                    @else
                        <span style="color: #6b7280; font-weight: 600;">{{ ucfirst($registration->status) }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if($registration->status === 'pending_payment')
        <div style="background-color: #fef3c7; padding: 20px; border-left: 4px solid #f59e0b; margin: 25px 0; border-radius: 6px;">
            <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #92400e;">
                💳 Pagamento Richiesto
            </h3>
            <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
                Per completare la tua registrazione, è necessario effettuare il pagamento di
                <strong>€{{ $event->getPriceForUser($user) }}</strong>.
                Accedi alla tua dashboard per procedere con il pagamento.
            </p>
        </div>
    @endif

    <h3 style="margin: 30px 0 15px 0; font-size: 20px; font-weight: 600; color: #1f2937;">
        🎯 Prossimi Passi
    </h3>

    <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <ol style="margin: 0; padding-left: 20px; font-size: 15px; line-height: 2; color: #4b5563;">
            @if($registration->status === 'pending_payment')
                <li><strong>Completa il pagamento</strong> accedendo alla tua dashboard</li>
            @endif
            <li><strong>Accedi alla dashboard</strong> tramite il link ricevuto via email</li>
            <li><strong>Scarica il tuo QR code</strong> per il check-in all'evento</li>
            <li><strong>Aggiungi l'evento al calendario</strong> per non dimenticarlo</li>
            <li><strong>Preparati per l'evento</strong> e divertiti! 🎉</li>
        </ol>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <a href="{{ route('guest.dashboard', ['token' => $user->guest_token]) }}" class="button" style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">
            🎫 Vai alla Dashboard
        </a>
    </div>

    <hr style="border: 0; height: 1px; background-color: #e5e7eb; margin: 30px 0;">

    <p style="font-size: 14px; color: #6b7280; text-align: center;">
        Per qualsiasi domanda o assistenza, non esitare a contattarci:<br>
        📧 <a href="mailto:{{ config('mail.from.address') }}" style="color: #f43f5e; text-decoration: none;">{{ config('mail.from.address') }}</a>
    </p>
@endsection
