@extends('emails.layout')

@section('title', 'Promemoria Evento')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-block; width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <span style="font-size: 40px;">⏰</span>
        </div>
        <h2 style="color: #1f2937; margin: 0; font-size: 28px; font-weight: 700;">Il tuo evento è tra 3 giorni!</h2>
    </div>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        Ciao <strong>{{ $user->name }}</strong>,
    </p>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 30px;">
        Questo è un promemoria per ricordarti che l'evento <strong>{{ $event->name }}</strong>
        si terrà tra soli <strong style="color: #f59e0b;">3 giorni</strong>! 🎉
    </p>

    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%); padding: 25px; border-radius: 12px; margin: 30px 0; text-align: center; box-shadow: 0 4px 6px rgba(251, 146, 60, 0.2);">
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #92400e; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
            📅 Data e Ora
        </p>
        <p style="margin: 0; font-size: 32px; font-weight: 700; color: #92400e; line-height: 1.2;">
            {{ $event->start_date->format('d/m/Y') }}
        </p>
        @if($event->start_date->format('H:i') !== '00:00')
            <p style="margin: 10px 0 0 0; font-size: 24px; font-weight: 600; color: #b45309;">
                {{ $event->start_date->format('H:i') }}
            </p>
        @endif
        @if($event->end_date && $event->end_date->format('Y-m-d') !== $event->start_date->format('Y-m-d'))
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #92400e;">
                Fino al {{ $event->end_date->format('d/m/Y') }}
            </p>
        @endif
    </div>

    <div style="background-color: #fdf2f8; padding: 20px; border-left: 4px solid #f43f5e; margin: 25px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #9333ea;">
            📍 Come Raggiungerci
        </h3>
        <p style="margin: 0 0 10px 0; font-size: 16px; color: #1f2937; font-weight: 600;">
            {{ $event->location }}
        </p>
        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($event->location) }}"
           style="display: inline-block; margin-top: 10px; padding: 8px 16px; background-color: #9333ea; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
            🗺️ Apri in Google Maps
        </a>
    </div>

    @if($event->description)
        <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 25px 0;">
            <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: 600; color: #1f2937;">
                ℹ️ Informazioni Evento
            </h3>
            <p style="margin: 0; font-size: 15px; color: #4b5563; line-height: 1.6;">
                {{ $event->description }}
            </p>
        </div>
    @endif

    <h3 style="margin: 30px 0 15px 0; font-size: 20px; font-weight: 600; color: #1f2937;">
        ✅ Checklist Pre-Evento
    </h3>

    <div style="background-color: #f0f9ff; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <ul style="margin: 0; padding-left: 20px; font-size: 15px; line-height: 2; color: #1e40af;">
            <li><strong>Scarica il tuo QR code</strong> e salvalo sul telefono (o stampalo)</li>
            <li><strong>Porta un documento di identità</strong> per la verifica all'ingresso</li>
            <li><strong>Arriva con 15 minuti di anticipo</strong> per il check-in</li>
            <li><strong>Indossa abbigliamento comodo</strong> adatto all'attività</li>
            <li><strong>Porta una bottiglia d'acqua</strong> per mantenerti idratato</li>
        </ul>
    </div>

    <div style="background-color: #fef3c7; padding: 20px; border-left: 4px solid #f59e0b; margin: 25px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #92400e;">
            🅿️ Parcheggio e Trasporti
        </h3>
        <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.6;">
            Parcheggio disponibile nelle vicinanze. Ti consigliamo di arrivare in anticipo per trovare posto.
            Mezzi pubblici: verifica gli orari sul sito del comune o usa Google Maps per il percorso migliore.
        </p>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <a href="{{ route('guest.dashboard', ['token' => $user->guest_token]) }}" class="button" style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; margin-bottom: 15px;">
            📱 Visualizza QR Code
        </a>
        <br>
        <a href="{{ route('events.show', $event->id) }}" style="color: #9333ea; text-decoration: none; font-size: 14px;">
            📅 Aggiungi al Calendario
        </a>
    </div>

    <div style="background-color: #f0fdf4; padding: 20px; border-left: 4px solid #10b981; margin: 25px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #065f46;">
            📞 Hai Bisogno di Aiuto?
        </h3>
        <p style="margin: 0 0 10px 0; font-size: 14px; color: #065f46; line-height: 1.6;">
            Se hai domande o necessiti di informazioni aggiuntive, siamo qui per aiutarti:
        </p>
        <p style="margin: 0; font-size: 14px; color: #065f46;">
            📧 Email: <a href="mailto:{{ config('mail.from.address') }}" style="color: #10b981; text-decoration: none; font-weight: 600;">{{ config('mail.from.address') }}</a>
        </p>
    </div>

    <hr style="border: 0; height: 1px; background-color: #e5e7eb; margin: 30px 0;">

    <p style="font-size: 16px; color: #1f2937; text-align: center; font-weight: 600; margin-bottom: 10px;">
        Non vediamo l'ora di vederti! 🎊
    </p>
    <p style="font-size: 14px; color: #6b7280; text-align: center; margin: 0;">
        Ti aspettiamo {{ $event->start_date->format('d/m/Y') }}
        @if($event->start_date->format('H:i') !== '00:00')
            alle {{ $event->start_date->format('H:i') }}
        @endif
    </p>
@endsection
