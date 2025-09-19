@extends('emails.layout')

@section('title', 'Conferma Pagamento')

@section('content')
    <h2>Ciao {{ $user->name }},</h2>

    <div class="success">
        <strong>✅ Il tuo pagamento è stato completato con successo!</strong>
    </div>

    <p>Ti confermiamo che abbiamo ricevuto il tuo pagamento per:</p>

    <div class="highlight">
        <h3>Dettagli del Pagamento</h3>
        <table style="width: 100%; margin: 10px 0;">
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 8px 0; font-weight: bold;">Corso/Servizio:</td>
                <td style="padding: 8px 0;">{{ $course ? $course->name : $payment->payment_type_name }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 8px 0; font-weight: bold;">Importo:</td>
                <td style="padding: 8px 0; color: #28a745; font-weight: bold;">€ {{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 8px 0; font-weight: bold;">Data Pagamento:</td>
                <td style="padding: 8px 0;">{{ $payment->payment_date->format('d/m/Y H:i') }}</td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 8px 0; font-weight: bold;">Metodo:</td>
                <td style="padding: 8px 0;">{{ $payment->payment_method_name }}</td>
            </tr>
            @if($payment->receipt_number)
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 8px 0; font-weight: bold;">Ricevuta N°:</td>
                <td style="padding: 8px 0; font-family: monospace;">{{ $payment->receipt_number }}</td>
            </tr>
            @endif
            @if($payment->transaction_id)
            <tr>
                <td style="padding: 8px 0; font-weight: bold;">ID Transazione:</td>
                <td style="padding: 8px 0; font-family: monospace; font-size: 12px;">{{ $payment->transaction_id }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if($course)
    <div class="highlight">
        <h3>Informazioni sul Corso</h3>
        <p><strong>Nome:</strong> {{ $course->name }}</p>
        @if($course->instructor)
            <p><strong>Istruttore:</strong> {{ $course->instructor->name }}</p>
        @endif
        @if($course->location)
            <p><strong>Luogo:</strong> {{ $course->location }}</p>
        @endif
        @if($course->start_date)
            <p><strong>Inizio:</strong> {{ $course->start_date->format('d/m/Y') }}</p>
        @endif
        @if($course->schedule)
            <p><strong>Orari:</strong> {{ $course->schedule }}</p>
        @endif
    </div>
    @endif

    <p style="margin: 30px 0 20px 0;">
        <strong>Cosa fare ora:</strong>
    </p>

    <ul style="line-height: 1.8;">
        <li>La tua iscrizione è ora attiva e confermata</li>
        @if($course && $course->start_date && $course->start_date->isFuture())
            <li>Le lezioni inizieranno il {{ $course->start_date->format('d/m/Y') }}</li>
        @else
            <li>Puoi iniziare a partecipare alle lezioni</li>
        @endif
        <li>Conserva questa email come ricevuta di pagamento</li>
        <li>Accedi al tuo account per vedere tutti i dettagli e il programma</li>
    </ul>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ route('student.my-courses') }}" class="button">
            Visualizza i Miei Corsi
        </a>
    </div>

    @if($payment->notes)
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Note:</strong></p>
        <p style="font-style: italic;">{{ $payment->notes }}</p>
    </div>
    @endif

    <p style="margin-top: 30px;">
        Grazie per aver scelto la nostra scuola di danza! 🩰<br>
        Non vediamo l'ora di vederti in sala.
    </p>

    <p style="font-size: 14px; color: #666; margin-top: 20px;">
        <em>Se hai domande o hai bisogno di assistenza, non esitare a contattarci tramite il sistema di messaggi nella tua area riservata o rispondendo a questa email.</em>
    </p>
@endsection