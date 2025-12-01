@extends('emails.layout')

@section('title', 'Accedi al tuo account')

@section('content')
    <h2 style="color: #1f2937; margin-bottom: 20px;">Ciao {{ $user->name }}! 👋</h2>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        Grazie per esserti registrato all'evento <strong>{{ $event->name }}</strong>!
    </p>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 30px;">
        Per accedere alla tua area riservata e visualizzare i dettagli della tua registrazione,
        clicca sul pulsante qui sotto. Non è richiesta alcuna password: il link ti permetterà di
        accedere direttamente al tuo account.
    </p>

    <div style="text-align: center; margin: 40px 0;">
        <a href="{{ $magicLink }}" class="button" style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">
            🔓 Accedi al tuo Account
        </a>
    </div>

    <div class="highlight" style="background: #fdf2f8; padding: 20px; border-left: 4px solid #f43f5e; margin: 30px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 600; color: #9333ea;">
            📅 Dettagli Evento
        </h3>
        <p style="margin: 5px 0; font-size: 15px; color: #6b7280;">
            <strong style="color: #1f2937;">Evento:</strong> {{ $event->name }}
        </p>
        <p style="margin: 5px 0; font-size: 15px; color: #6b7280;">
            <strong style="color: #1f2937;">Data:</strong> {{ $event->start_date->format('d/m/Y') }}
            @if($event->start_date->format('H:i') !== '00:00')
                alle {{ $event->start_date->format('H:i') }}
            @endif
        </p>
        <p style="margin: 5px 0; font-size: 15px; color: #6b7280;">
            <strong style="color: #1f2937;">Luogo:</strong> {{ $event->location }}
        </p>
    </div>

    <div style="background-color: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; margin: 25px 0; border-radius: 6px;">
        <p style="margin: 0; font-size: 14px; color: #92400e;">
            ⏰ <strong>Nota importante:</strong> Questo link di accesso è valido per 180 giorni.
            Puoi usarlo ogni volta che vuoi accedere al tuo account senza bisogno di password.
        </p>
    </div>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-top: 30px;">
        Una volta effettuato l'accesso, potrai:
    </p>

    <ul style="font-size: 16px; line-height: 1.8; color: #4b5563; margin-bottom: 30px;">
        <li>Visualizzare i dettagli della tua registrazione</li>
        <li>Scaricare il tuo QR code per il check-in</li>
        <li>Completare il pagamento se necessario</li>
        <li>Gestire i tuoi dati e consensi privacy</li>
    </ul>

    <hr style="border: 0; height: 1px; background-color: #e5e7eb; margin: 30px 0;">

    <p style="font-size: 14px; color: #6b7280; margin-bottom: 10px;">
        Hai bisogno di aiuto? Contattaci a:
    </p>
    <p style="font-size: 14px; color: #6b7280; margin: 0;">
        📧 Email: <a href="mailto:{{ config('mail.from.address') }}" style="color: #f43f5e; text-decoration: none;">{{ config('mail.from.address') }}</a>
    </p>
@endsection
