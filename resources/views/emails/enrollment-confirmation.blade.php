@extends('emails.layout')

@section('title', 'Iscrizione Confermata!')

@section('content')
<h2>Iscrizione Confermata! 🎉</h2>

<p>Ciao <strong>{{ $user->first_name }}</strong>,</p>

<p>La tua iscrizione al corso è stata confermata con successo!</p>

<div class="success">
    <h3>📚 Dettagli del Corso:</h3>
    <ul>
        <li><strong>Nome:</strong> {{ $course->name }}</li>
        <li><strong>Descrizione:</strong> {{ $course->description }}</li>
        <li><strong>Livello:</strong> {{ ucfirst($course->level) }}</li>
        <li><strong>Durata:</strong> {{ $course->duration_weeks }} settimane</li>
        <li><strong>Prezzo:</strong> €{{ $course->price }}</li>
        @if($course->start_date)
        <li><strong>Inizio:</strong> {{ $course->start_date->format('d/m/Y') }}</li>
        @endif
        @if($course->schedule)
        <li><strong>Orario:</strong> {{ $course->schedule }}</li>
        @endif
    </ul>
</div>

<div class="highlight">
    <h3>🎯 Prossimi Passi:</h3>
    <ol>
        <li>Segna la data di inizio sul tuo calendario</li>
        <li>Prepara tutto il necessario per le lezioni</li>
        @if($enrollment->status === 'pending_payment')
        <li><strong>Completa il pagamento entro {{ $enrollment->created_at->addDays(7)->format('d/m/Y') }}</strong></li>
        @endif
    </ol>
</div>

<p style="text-align: center;">
    <a href="{{ route('student.my-courses.show', $enrollment) }}" class="button">Visualizza Dettagli Iscrizione</a>
</p>

@if($enrollment->status === 'pending_payment')
<p><em><strong>Nota:</strong> Ricorda di completare il pagamento per confermare definitivamente la tua partecipazione.</em></p>
@endif

<p>Non vediamo l'ora di vederti in aula! 💃</p>

<p><strong>Lo Staff di {{ $course->school->name }}</strong></p>
@endsection