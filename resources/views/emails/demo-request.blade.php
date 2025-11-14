<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-row { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #ec4899; border-radius: 5px; }
        .label { font-weight: bold; color: #ec4899; display: block; margin-bottom: 5px; }
        .value { color: #333; font-size: 16px; }
        .footer { text-align: center; margin-top: 20px; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">🎯 Nuova Richiesta Demo</h1>
            <p style="margin: 10px 0 0 0;">DanzaFacile</p>
        </div>
        
        <div class="content">
            <p style="font-size: 18px; margin-top: 0;">Hai ricevuto una nuova richiesta demo!</p>
            
            <div class="info-row">
                <span class="label">👤 Nome:</span>
                <span class="value">{{ $data['name'] }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">📧 Email:</span>
                <span class="value">{{ $data['email'] }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">📱 Telefono:</span>
                <span class="value">{{ $data['phone'] }}</span>
            </div>
            
            @if(!empty($data['school_name']))
            <div class="info-row">
                <span class="label">🏫 Nome Scuola:</span>
                <span class="value">{{ $data['school_name'] }}</span>
            </div>
            @endif
            
            @if(!empty($data['students_count']))
            <div class="info-row">
                <span class="label">👥 Numero Studenti:</span>
                <span class="value">{{ $data['students_count'] }}</span>
            </div>
            @endif
            
            @if(!empty($data['message']))
            <div class="info-row">
                <span class="label">💬 Messaggio:</span>
                <span class="value">{{ $data['message'] }}</span>
            </div>
            @endif
            
            <div class="info-row">
                <span class="label">🕐 Data/Ora:</span>
                <span class="value">{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
            
            <p style="margin-top: 30px; padding: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
                <strong>⚡ Azione richiesta:</strong> Contatta il cliente entro 24 ore per massimizzare le conversioni!
            </p>
        </div>
        
        <div class="footer">
            <p>Email automatica da DanzaFacile | www.danzafacile.it</p>
        </div>
    </div>
</body>
</html>
