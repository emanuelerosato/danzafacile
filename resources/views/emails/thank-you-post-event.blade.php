@extends('emails.layout')

@section('title', 'Grazie per la partecipazione')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-block; width: 80px; height: 80px; background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
            <span style="font-size: 40px;">🎉</span>
        </div>
        <h2 style="color: #1f2937; margin: 0; font-size: 28px; font-weight: 700;">Grazie per aver partecipato!</h2>
    </div>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 20px;">
        Ciao <strong>{{ $user->name }}</strong>,
    </p>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; margin-bottom: 30px;">
        Grazie per aver partecipato a <strong>{{ $event->name }}</strong>!
        Speriamo che tu abbia vissuto un'esperienza indimenticabile e che ti sia divertito insieme a noi. 💜
    </p>

    <div style="background: linear-gradient(135deg, #fdf2f8 0%, #fae8ff 100%); padding: 30px; border-radius: 12px; margin: 30px 0; text-align: center;">
        <p style="margin: 0; font-size: 18px; color: #9333ea; font-weight: 600; line-height: 1.6;">
            "La danza è il linguaggio nascosto dell'anima"<br>
            <span style="font-size: 14px; color: #a855f7; font-weight: 400;">- Martha Graham</span>
        </p>
    </div>

    <div style="background-color: #fdf2f8; padding: 25px; border-left: 4px solid #f43f5e; margin: 25px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 15px 0; font-size: 20px; font-weight: 600; color: #9333ea;">
            📝 Recap del tuo Evento
        </h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; font-size: 15px; color: #6b7280; width: 35%;">
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
            @if($registration->checked_in_at)
            <tr>
                <td style="padding: 8px 0; font-size: 15px; color: #6b7280;">
                    <strong style="color: #1f2937;">Check-in:</strong>
                </td>
                <td style="padding: 8px 0; font-size: 15px; color: #10b981; font-weight: 600;">
                    ✓ {{ $registration->checked_in_at->format('d/m/Y H:i') }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <h3 style="margin: 30px 0 15px 0; font-size: 20px; font-weight: 600; color: #1f2937; text-align: center;">
        💭 Raccontaci la tua esperienza
    </h3>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; text-align: center; margin-bottom: 25px;">
        Il tuo feedback è prezioso per noi! Aiutaci a migliorare compilando un breve sondaggio.<br>
        Ci vorranno solo <strong>2 minuti</strong>. 🙏
    </p>

    <div style="text-align: center; margin: 40px 0;">
        <a href="#" class="button" style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">
            ⭐ Lascia il tuo Feedback
        </a>
    </div>

    <div style="background-color: #f0f9ff; padding: 25px; border-radius: 12px; margin: 30px 0;">
        <h3 style="margin: 0 0 15px 0; font-size: 18px; font-weight: 600; color: #1e40af; text-align: center;">
            📣 Condividi sui Social
        </h3>
        <p style="margin: 0 0 20px 0; font-size: 15px; color: #1e40af; text-align: center; line-height: 1.6;">
            Hai scattato foto o video durante l'evento? Condividili con noi sui social!<br>
            Usa l'hashtag <strong style="color: #2563eb;">#{{ str_replace(' ', '', $event->name) }}</strong>
        </p>
        <div style="text-align: center;">
            <a href="#" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                📘 Facebook
            </a>
            <a href="#" style="display: inline-block; margin: 0 10px; padding: 10px 20px; background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                📸 Instagram
            </a>
        </div>
    </div>

    <hr style="border: 0; height: 1px; background-color: #e5e7eb; margin: 30px 0;">

    <h3 style="margin: 0 0 15px 0; font-size: 20px; font-weight: 600; color: #1f2937; text-align: center;">
        🎊 Prossimi Eventi
    </h3>

    <p style="font-size: 16px; line-height: 1.6; color: #4b5563; text-align: center; margin-bottom: 25px;">
        Abbiamo tanti altri eventi in programma! Continua a seguirci per non perdere le prossime opportunità.
    </p>

    <div style="background: linear-gradient(135deg, #fdf2f8 0%, #fae8ff 100%); padding: 25px; border-radius: 12px; margin: 25px 0; text-align: center;">
        <p style="margin: 0 0 15px 0; font-size: 16px; color: #9333ea; font-weight: 600;">
            🔔 Resta aggiornato!
        </p>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6b7280; line-height: 1.6;">
            Iscriviti alla nostra newsletter per ricevere in anteprima le novità,<br>
            gli eventi esclusivi e le promozioni riservate.
        </p>
        <a href="{{ route('guest.dashboard', ['token' => $user->guest_token]) }}" style="display: inline-block; padding: 12px 30px; background-color: #9333ea; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">
            📬 Iscriviti alla Newsletter
        </a>
    </div>

    <div style="text-align: center; margin: 40px 0;">
        <a href="{{ route('events.index') }}" class="button" style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%); color: #ffffff !important; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px;">
            👀 Scopri Altri Eventi
        </a>
    </div>

    <div style="background-color: #f0fdf4; padding: 20px; border-left: 4px solid #10b981; margin: 25px 0; border-radius: 6px;">
        <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #065f46;">
            💝 Hai una domanda o suggerimento?
        </h3>
        <p style="margin: 0; font-size: 14px; color: #065f46; line-height: 1.6;">
            Siamo sempre felici di ricevere il tuo feedback! Scrivici a:<br>
            📧 <a href="mailto:{{ config('mail.from.address') }}" style="color: #10b981; text-decoration: none; font-weight: 600;">{{ config('mail.from.address') }}</a>
        </p>
    </div>

    <hr style="border: 0; height: 1px; background-color: #e5e7eb; margin: 30px 0;">

    <div style="text-align: center; padding: 30px 0;">
        <p style="font-size: 20px; color: #9333ea; font-weight: 600; margin-bottom: 10px;">
            Grazie ancora per essere stato con noi! 💜
        </p>
        <p style="font-size: 16px; color: #6b7280; margin: 0;">
            Alla prossima avventura insieme!
        </p>
    </div>
@endsection
