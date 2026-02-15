@component('mail::message')
# Ricevuta Pagamento

Gentile **{{ $student->full_name }}**,

Ti inviamo in allegato la ricevuta per il pagamento effettuato.

@component('mail::panel')
## Dettagli Pagamento

**Ricevuta N°:** {{ $payment->receipt_number }}
**Data:** {{ $payment->payment_date->format('d/m/Y H:i') }}
**Importo:** **€ {{ number_format($payment->amount, 2, ',', '.') }}**

**Metodo di Pagamento:** {{ $payment->payment_method_name }}

@if($payment->course)
**Corso:** {{ $payment->course->name }}
@elseif($payment->event)
**Evento:** {{ $payment->event->name }}
@else
**Tipo:** {{ $payment->payment_type_name }}
@endif
@endcomponent

@if($payment->notes)
**Note:** {{ $payment->notes }}
@endif

La ricevuta in formato PDF è allegata a questa email.

---

Grazie per aver scelto {{ $school->name }}.

Cordiali saluti,
**{{ $school->name }}**

@if($school->phone)
📞 {{ $school->phone }}
@endif

@if($school->email)
✉️ {{ $school->email }}
@endif

@if($school->website)
🌐 [{{ $school->website }}]({{ $school->website }})
@endif

@component('mail::subcopy')
Questa è una email automatica inviata dal sistema di gestione di {{ $school->name }}.
Ricevuta generata il {{ now()->format('d/m/Y H:i') }}.
@endcomponent
@endcomponent
