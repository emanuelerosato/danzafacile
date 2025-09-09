@extends('emails.layout')

@section('title', 'Benvenuto!')

@section('content')
<h2>Ciao {{ $user->first_name }}! 👋</h2>

<p>Benvenuto nella <strong>{{ $user->school->name ?? 'nostra Scuola di Danza' }}</strong>!</p>

<p>Il tuo account è stato creato con successo. Ora puoi:</p>

<ul>
    <li>🩰 Iscriverti ai corsi disponibili</li>
    <li>📅 Visualizzare i tuoi orari</li>
    <li>💳 Gestire i pagamenti</li>
    <li>📄 Caricare documenti necessari</li>
    <li>👤 Aggiornare il tuo profilo</li>
</ul>

<div class="success">
    <strong>I tuoi dati di accesso:</strong><br>
    Email: <strong>{{ $user->email }}</strong><br>
    <em>Usa la password che hai scelto durante la registrazione</em>
</div>

<p style="text-align: center;">
    <a href="{{ route('login') }}" class="button">Accedi alla Dashboard</a>
</p>

<p>Se hai domande o hai bisogno di assistenza, non esitare a contattarci.</p>

<p>Buona danza! 💃</p>

<p><strong>Lo Staff della Scuola</strong></p>
@endsection