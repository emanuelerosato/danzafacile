<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Email 1 - Benvenuto + Identificazione Problema',
                'slug' => 'welcome-problem',
                'sequence_order' => 1,
                'delay_days' => 0,
                'subject' => '{{Nome}}, ecco cosa NON funziona nella gestione della tua scuola...',
                'body' => $this->getEmail1Body(),
                'is_active' => true,
                'notes' => 'Prima email del funnel - Invio immediato dopo iscrizione. Focus: identificare il problema (caos amministrativo, tempo perso).',
            ],
            [
                'name' => 'Email 2 - Agitazione Problema + Soluzione',
                'slug' => 'agitate-solution',
                'sequence_order' => 2,
                'delay_days' => 2,
                'subject' => '{{Nome}}, stai ancora facendo QUESTO errore?',
                'body' => $this->getEmail2Body(),
                'is_active' => true,
                'notes' => 'Seconda email - Giorno +2. Focus: amplificare il dolore (4 ore al giorno perse, errori pagamenti). Introdurre soluzione.',
            ],
            [
                'name' => 'Email 3 - Social Proof + Autorità',
                'slug' => 'social-proof',
                'sequence_order' => 3,
                'delay_days' => 5,
                'subject' => 'Come Daniela ha recuperato 20 ore a settimana',
                'body' => $this->getEmail3Body(),
                'is_active' => true,
                'notes' => 'Terza email - Giorno +5. Focus: storia di successo, testimonianza, risultati concreti.',
            ],
            [
                'name' => 'Email 4 - Offerta + Scarcity',
                'slug' => 'offer-scarcity',
                'sequence_order' => 4,
                'delay_days' => 9,
                'subject' => '{{Nome}}, ultimi 3 giorni per il 1° mese GRATIS',
                'body' => $this->getEmail4Body(),
                'is_active' => true,
                'notes' => 'Quarta email - Giorno +9. Focus: offerta limitata, lista benefici, garanzia, CTA forte.',
            ],
            [
                'name' => 'Email 5 - Last Chance + FOMO',
                'slug' => 'last-chance',
                'sequence_order' => 5,
                'delay_days' => 14,
                'subject' => 'ULTIMA CHIAMATA: Setup gratuito scade stasera ({{Nome}})',
                'body' => $this->getEmail5Body(),
                'is_active' => true,
                'notes' => 'Quinta email - Giorno +14. Focus: urgenza massima, FOMO, cosa perderai se non agisci.',
            ],
        ];

        foreach ($templates as $template) {
            DB::table('email_templates')->insert(array_merge($template, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function getEmail1Body(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.8; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); color: white; padding: 40px 20px; text-align: center; }
        .content { padding: 30px 20px; background: #ffffff; }
        .highlight { background: #fef3c7; padding: 20px; border-left: 4px solid #f59e0b; margin: 20px 0; }
        .cta { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); color: white; padding: 15px 30px; text-decoration: none; display: inline-block; border-radius: 8px; font-weight: bold; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; border-top: 1px solid #eee; }
        ul { padding-left: 20px; }
        li { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">Ciao {{Nome}},</h1>
        <p style="margin: 10px 0 0 0;">Sono Daniela Crescenzio, fondatrice di DanzaFacile</p>
    </div>

    <div class="content">
        <p>Lascia che ti faccia una domanda scomoda...</p>

        <p><strong>Quante ore perdi OGNI SETTIMANA a:</strong></p>
        <ul>
            <li>Cercare fogli volanti con le presenze?</li>
            <li>Rispondere a 50 messaggi WhatsApp su orari e pagamenti?</li>
            <li>Ricordare a mano chi deve ancora pagare?</li>
            <li>Gestire iscrizioni su Excel che si corrompono?</li>
            <li>Fare manualmente certificati e documenti?</li>
        </ul>

        <div class="highlight">
            <strong>La verità brutale:</strong> La maggior parte dei titolari di scuole di danza perde dalle <strong>15 alle 20 ore OGNI SETTIMANA</strong> in attività amministrative che potrebbero essere automatizzate.
        </div>

        <p>20 ore sono <strong>metà di una settimana lavorativa</strong>.</p>

        <p>Tempo che potresti dedicare a:</p>
        <ul>
            <li>Creare nuovi corsi</li>
            <li>Formare meglio i tuoi insegnanti</li>
            <li>Fare marketing per attrarre nuovi studenti</li>
            <li>O semplicemente... <strong>vivere la tua vita</strong></li>
        </ul>

        <p>Il problema non sei tu.</p>

        <p>Il problema è che stai usando strumenti del secolo scorso (Excel, carta, WhatsApp) per gestire una scuola del 2025.</p>

        <p><strong>C'è un modo migliore.</strong></p>

        <p>Te lo mostro nella prossima email (tra 2 giorni).</p>

        <a href="https://www.danzafacile.it" class="cta">👉 Oppure clicca qui per vedere subito la demo</a>

        <p style="margin-top: 30px;">A presto,<br>
        <strong>Daniela Crescenzio</strong><br>
        Fondatrice DanzaFacile<br>
        📱 +39 340 929 5364</p>

        <p style="font-size: 12px; color: #999; margin-top: 20px;">P.S. Ho creato DanzaFacile perché anch'io ero stanca di perdere ore in burocrazia invece di ballare. Ora gestisco tutto in 10 minuti al giorno. E tu?</p>
    </div>

    <div class="footer">
        <p>DanzaFacile - Il Software per Scuole di Danza<br>
        www.danzafacile.it | P.IVA 03003220732</p>
        <p style="font-size: 10px; margin-top: 10px;">
            Hai ricevuto questa email perché hai richiesto una demo su DanzaFacile.<br>
            Non vuoi più ricevere queste email? <a href="#">Clicca qui per cancellarti</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getEmail2Body(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.8; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); color: white; padding: 40px 20px; text-align: center; }
        .content { padding: 30px 20px; background: #ffffff; }
        .warning-box { background: #fee2e2; border-left: 4px solid #dc2626; padding: 20px; margin: 20px 0; }
        .solution-box { background: #d1fae5; border-left: 4px solid #059669; padding: 20px; margin: 20px 0; }
        .cta { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); color: white; padding: 15px 30px; text-decoration: none; display: inline-block; border-radius: 8px; font-weight: bold; margin: 20px 0; font-size: 18px; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; border-top: 1px solid #eee; }
        .checklist { background: #f9fafb; padding: 20px; border-radius: 8px; }
        .checklist li { margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">{{Nome}}, questo errore ti costa €500/mese</h1>
    </div>

    <div class="content">
        <p>Facciamo due conti veloci...</p>

        <div class="warning-box">
            <strong>⚠️ Il costo nascosto del "faccio tutto a mano":</strong><br><br>

            • <strong>20 ore/settimana</strong> × 4 settimane = 80 ore/mese<br>
            • Valore del tuo tempo come titolare: almeno <strong>€15/ora</strong><br>
            • 80 ore × €15 = <strong>€1.200 al mese</strong> di tempo perso<br><br>

            E questo <strong>senza contare</strong>:<br>
            • Errori nei pagamenti (mediamente €200-300/mese non riscossi)<br>
            • Studenti persi per disorganizzazione<br>
            • Stress, burnout, errori umani
        </div>

        <p>Ma aspetta... c'è di peggio.</p>

        <p><strong>L'errore più grande</strong> che vedo fare ai titolari di scuole di danza è questo:</p>

        <p style="font-size: 20px; font-weight: bold; text-align: center; color: #dc2626;">
            "Lo farò quando avrò più tempo"
        </p>

        <p>Spoiler: <strong>quel momento non arriverà MAI</strong>.</p>

        <p>Perché? Perché se continui a fare tutto manualmente, sarai SEMPRE sommerso dal lavoro.</p>

        <p>È un circolo vizioso:</p>
        <ol>
            <li>Sei troppo occupato per organizzarti meglio</li>
            <li>Resti disorganizzato</li>
            <li>Perdi ancora più tempo</li>
            <li>Sei ancora più occupato</li>
            <li>Ripeti dal punto 1</li>
        </ol>

        <div class="solution-box">
            <strong>✅ La soluzione è spezzare il circolo ADESSO</strong><br><br>

            DanzaFacile è stato progettato esattamente per questo:<br><br>

            <div class="checklist">
                ✓ <strong>Setup in 20 minuti</strong> (non giorni o settimane)<br>
                ✓ <strong>Formazione inclusa</strong> (ti guidiamo passo-passo)<br>
                ✓ <strong>Importazione automatica</strong> dei tuoi dati attuali<br>
                ✓ <strong>Da domani</strong> inizi a risparmiare tempo
            </div>
        </div>

        <p><strong>Cosa succede se attivi DanzaFacile questa settimana:</strong></p>

        <p>✨ <strong>Giorno 1:</strong> Import studenti e corsi (15 minuti)<br>
        ✨ <strong>Giorno 2:</strong> Prime presenze automatiche<br>
        ✨ <strong>Giorno 7:</strong> Primo promemoria pagamenti automatico<br>
        ✨ <strong>Giorno 30:</strong> Hai recuperato 15+ ore del tuo tempo</p>

        <a href="https://www.danzafacile.it" class="cta">🚀 SÌ, VOGLIO INIZIARE ORA</a>

        <p style="margin-top: 30px;">Non aspettare di avere tempo.<br>
        <strong>Crea</strong> il tempo.</p>

        <p>A presto,<br>
        <strong>Daniela Crescenzio</strong><br>
        📱 +39 340 929 5364</p>

        <p style="font-size: 12px; color: #999; margin-top: 20px;">P.S. Tra 3 giorni ti racconto come una titolare di scuola ha recuperato 20 ore a settimana. Spoiler: ora ha aperto una seconda sede.</p>
    </div>

    <div class="footer">
        <p>DanzaFacile - Il Software per Scuole di Danza<br>
        www.danzafacile.it | P.IVA 03003220732</p>
    </div>
</body>
</html>
HTML;
    }

    private function getEmail3Body(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.8; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); color: white; padding: 40px 20px; text-align: center; }
        .content { padding: 30px 20px; background: #ffffff; }
        .testimonial { background: #f0f9ff; border-left: 4px solid #0284c7; padding: 25px; margin: 25px 0; font-style: italic; }
        .stats-box { background: #fef3c7; padding: 25px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .stat { font-size: 32px; font-weight: bold; color: #ec4899; }
        .cta { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); color: white; padding: 15px 30px; text-decoration: none; display: inline-block; border-radius: 8px; font-weight: bold; margin: 20px 0; font-size: 18px; }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; border-top: 1px solid #eee; }
        .before-after { display: table; width: 100%; margin: 20px 0; }
        .before, .after { display: table-cell; width: 50%; padding: 15px; }
        .before { background: #fee2e2; border-right: 2px solid #dc2626; }
        .after { background: #d1fae5; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">La Storia di Marina</h1>
        <p style="margin: 10px 0 0 0;">Da 50 ore/settimana a 30 ore/settimana</p>
    </div>

    <div class="content">
        <p>Ciao {{Nome}},</p>

        <p>Oggi voglio raccontarti la storia di Marina, titolare della "Scuola Armonia" a Milano.</p>

        <p><strong>Prima di DanzaFacile:</strong></p>

        <div class="testimonial">
            "Ero completamente sommersa. Gestivo 120 studenti con Excel e WhatsApp. Ogni settimana passavo almeno 4 ore solo a rincorrere i pagamenti. I genitori mi chiamavano in continuazione per sapere orari e presenze. Stavo seriamente pensando di chiudere la seconda sede perché non ce la facevo più."
            <br><br>
            <strong>- Marina T., Milano</strong>
        </div>

        <div class="stats-box">
            <div class="stat">↓ 40%</div>
            <p><strong>Riduzione ore lavoro amministrativo</strong></p>
            <p style="font-size: 14px; color: #666;">Da 20 ore/settimana a 12 ore/settimana</p>
        </div>

        <p><strong>Dopo 30 giorni con DanzaFacile:</strong></p>

        <ul>
            <li>✅ <strong>Zero telefonate</strong> per orari (studenti vedono tutto nell'app)</li>
            <li>✅ <strong>Pagamenti automatici</strong> con promemoria via email</li>
            <li>✅ <strong>Presenze in 2 click</strong> invece di 20 minuti</li>
            <li>✅ <strong>Report mensili</strong> generati automaticamente</li>
            <li>✅ <strong>Comunicazioni</strong> inviate a tutti in 30 secondi</li>
        </ul>

        <div class="before-after">
            <div class="before">
                <strong>❌ PRIMA</strong><br><br>
                • 20h/settimana amministrazione<br>
                • 4h/settimana pagamenti<br>
                • 50+ chiamate/settimana<br>
                • Stress quotidiano<br>
                • Errori frequenti
            </div>
            <div class="after">
                <strong>✅ DOPO</strong><br><br>
                • 12h/settimana amministrazione<br>
                • 30min/settimana pagamenti<br>
                • 5 chiamate/settimana<br>
                • Serenità ritrovata<br>
                • Zero errori
            </div>
        </div>

        <div class="testimonial">
            "Il ROI è stato immediato. Il primo mese ho recuperato €800 di pagamenti che altrimenti avrei dimenticato. DanzaFacile si è ripagato da solo in 2 settimane. Ora sto aprendo la terza sede."
            <br><br>
            <strong>- Marina T., Milano</strong>
        </div>

        <p><strong>Ma Marina non è un caso isolato.</strong></p>

        <p>Ecco i risultati medi dei nostri clienti nei primi 90 giorni:</p>

        <ul>
            <li>📊 <strong>+32%</strong> tasso di riscossione pagamenti</li>
            <li>⏰ <strong>-15 ore/settimana</strong> lavoro amministrativo</li>
            <li>😊 <strong>+85%</strong> soddisfazione genitori</li>
            <li>💰 <strong>€600-1.200/mese</strong> entrate recuperate</li>
        </ul>

        <p><strong>La domanda è:</strong></p>

        <p style="font-size: 18px; font-weight: bold; text-align: center; color: #ec4899;">
            Quanto tempo vuoi ancora perdere prima di iniziare?
        </p>

        <a href="https://www.danzafacile.it" class="cta">✨ INIZIA LA TUA TRASFORMAZIONE</a>

        <p style="background: #fef3c7; padding: 15px; border-radius: 8px; margin-top: 30px;">
            <strong>🎁 BONUS ESCLUSIVO:</strong> Se attivi entro 48 ore ricevi:<br>
            • Setup gratuito personalizzato (valore €200)<br>
            • 1° mese completamente GRATIS<br>
            • Formazione 1-to-1 con il nostro team
        </p>

        <p>A presto,<br>
        <strong>Daniela Crescenzio</strong><br>
        📱 +39 340 929 5364</p>

        <p style="font-size: 12px; color: #999; margin-top: 20px;">P.S. Tra 4 giorni ti svelo l'offerta speciale riservata solo ai nuovi iscritti. Non te la perdere.</p>
    </div>

    <div class="footer">
        <p>DanzaFacile - Il Software per Scuole di Danza<br>
        www.danzafacile.it | P.IVA 03003220732</p>
    </div>
</body>
</html>
HTML;
    }

    private function getEmail4Body(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.8; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 40px 20px; text-align: center; }
        .countdown { background: #fee2e2; border: 3px solid #dc2626; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
        .content { padding: 30px 20px; background: #ffffff; }
        .offer-box { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 3px solid #f59e0b; padding: 25px; margin: 25px 0; border-radius: 8px; text-align: center; }
        .price { font-size: 48px; font-weight: bold; color: #dc2626; }
        .old-price { text-decoration: line-through; color: #999; font-size: 24px; }
        .guarantee { background: #d1fae5; border-left: 4px solid #059669; padding: 20px; margin: 20px 0; }
        .cta { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 20px 40px; text-decoration: none; display: inline-block; border-radius: 8px; font-weight: bold; margin: 20px 0; font-size: 22px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .cta:hover { transform: scale(1.05); }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; border-top: 1px solid #eee; }
        .benefit { padding: 10px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 32px;">⏰ ULTIMI 3 GIORNI</h1>
        <p style="margin: 10px 0 0 0; font-size: 18px;">{{Nome}}, questa offerta scade tra 72 ore</p>
    </div>

    <div class="content">
        <p>Ciao {{Nome}},</p>

        <p>Sarò diretta: <strong>questa è la tua ultima occasione</strong> per ottenere DanzaFacile alle condizioni speciali riservate ai nuovi iscritti.</p>

        <div class="countdown">
            <h2 style="margin: 0; color: #dc2626;">⏰ SCADENZA OFFERTA</h2>
            <p style="font-size: 32px; font-weight: bold; margin: 10px 0;">72 ORE</p>
            <p style="margin: 0;">Poi tornerai a pagare il prezzo pieno</p>
        </div>

        <div class="offer-box">
            <h3 style="margin-top: 0;">🎁 OFFERTA ESCLUSIVA NUOVI ISCRITTI</h3>

            <p class="old-price">€27/mese</p>
            <p class="price">€0</p>
            <p style="font-size: 20px; font-weight: bold; color: #059669;">PRIMO MESE GRATIS</p>

            <p style="margin: 20px 0;">Poi solo <strong>€7/mese</strong> (per sempre) se ti iscrivi entro 72 ore</p>
        </div>

        <p><strong>Cosa ottieni con DanzaFacile:</strong></p>

        <div class="benefit">✓ <strong>Gestione completa fino a 25 studenti</strong></div>
        <div class="benefit">✓ <strong>Presenze automatiche</strong> con QR code</div>
        <div class="benefit">✓ <strong>Promemoria pagamenti</strong> via email automatici</div>
        <div class="benefit">✓ <strong>App mobile</strong> per studenti (iOS + Android)</div>
        <div class="benefit">✓ <strong>Calendario corsi</strong> sincronizzato</div>
        <div class="benefit">✓ <strong>Documenti automatici</strong> (certificati, ricevute)</div>
        <div class="benefit">✓ <strong>Report e statistiche</strong> in tempo reale</div>
        <div class="benefit">✓ <strong>Comunicazioni di massa</strong> via email/SMS</div>
        <div class="benefit">✓ <strong>Backup automatico</strong> cloud sicuro</div>
        <div class="benefit">✓ <strong>Supporto dedicato</strong> via chat e telefono</div>

        <div style="margin: 30px 0; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <p style="margin: 0; font-size: 18px; font-weight: bold;">Valore totale: €527</p>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #666;">
                Setup (€200) + Formazione (€150) + Software (€177/anno) = €527
            </p>
        </div>

        <p style="text-align: center; font-size: 24px; font-weight: bold; color: #dc2626;">
            Tu paghi: €0 il primo mese
        </p>

        <div class="guarantee">
            <strong>🛡️ GARANZIA 100% SODDISFATTI O RIMBORSATI</strong><br><br>

            Se nei primi 30 giorni non sei completamente soddisfatto, ti rimborsiamo ogni centesimo. Senza domande.<br><br>

            <strong>Il rischio è tutto nostro.</strong>
        </div>

        <p style="text-align: center; margin: 40px 0;">
            <a href="https://www.danzafacile.it" class="cta">🚀 SÌ, VOGLIO ATTIVARE ORA</a>
        </p>

        <p><strong>Perché questa fretta?</strong></p>

        <p>Semplice: abbiamo solo <strong>30 posti disponibili</strong> al mese per garantire supporto personalizzato a tutti.</p>

        <p>Ne restano <strong>solo 7</strong>.</p>

        <p>Tra 72 ore l'offerta scade e tornerai a pagare il prezzo pieno di €27/mese.</p>

        <p><strong>Facciamo due conti:</strong></p>

        <ul>
            <li>Iscrivendoti ORA: €0 (1° mese) + €84/anno = <strong>€84 il primo anno</strong></li>
            <li>Iscrivendoti dopo: €27 × 12 mesi = <strong>€324/anno</strong></li>
        </ul>

        <p style="font-size: 20px; font-weight: bold; text-align: center; background: #fef3c7; padding: 15px; border-radius: 8px;">
            Risparmi €240 iscrivendoti ORA
        </p>

        <p style="text-align: center; margin: 40px 0;">
            <a href="https://www.danzafacile.it" class="cta">✨ ATTIVA SUBITO DANZAFACILE</a>
        </p>

        <p>Non aspettare.<br>
        I posti stanno finendo.</p>

        <p>A presto,<br>
        <strong>Daniela Crescenzio</strong><br>
        Fondatrice DanzaFacile<br>
        📱 +39 340 929 5364</p>

        <p style="font-size: 12px; color: #999; margin-top: 30px;">P.S. Questa è l'offerta migliore che faremo mai. Tra 72 ore non sarà più disponibile. <strong>Agisci ora.</strong></p>
    </div>

    <div class="footer">
        <p>DanzaFacile - Il Software per Scuole di Danza<br>
        www.danzafacile.it | P.IVA 03003220732</p>
    </div>
</body>
</html>
HTML;
    }

    private function getEmail5Body(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.8; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%); color: white; padding: 50px 20px; text-align: center; border: 5px solid #dc2626; }
        .urgent { background: #fee2e2; border: 3px solid #dc2626; padding: 25px; text-align: center; margin: 20px 0; border-radius: 8px; }
        .content { padding: 30px 20px; background: #ffffff; }
        .loss-list { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 20px 0; }
        .cta { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); color: white; padding: 25px 50px; text-decoration: none; display: inline-block; border-radius: 8px; font-weight: bold; margin: 20px 0; font-size: 24px; box-shadow: 0 6px 12px rgba(0,0,0,0.2); animation: pulse 2s infinite; }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; border-top: 1px solid #eee; }
        .countdown-big { font-size: 64px; font-weight: bold; color: #dc2626; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 40px;">🚨 ULTIMA CHIAMATA 🚨</h1>
        <p style="margin: 15px 0 0 0; font-size: 22px;">{{Nome}}, questa è la tua ULTIMA occasione</p>
    </div>

    <div class="content">
        <div class="urgent">
            <h2 style="margin: 0; color: #dc2626;">L'OFFERTA SCADE STASERA</h2>
            <p class="countdown-big">23:59</p>
            <p style="font-size: 18px; margin: 0;">Poi tornerai a pagare il prezzo pieno (€27/mese)</p>
        </div>

        <p style="font-size: 20px; font-weight: bold;">Ciao {{Nome}},</p>

        <p>Questa è l'ultima email che riceverai da me sull'offerta speciale.</p>

        <p><strong>A mezzanotte</strong>, l'offerta del 1° mese gratis + €7/mese a vita <strong>sparirà per sempre</strong>.</p>

        <p>E tornerai a pagare il prezzo pieno di €27/mese.</p>

        <p>So che probabilmente hai pensato:<br>
        <em>"Lo farò domani"</em><br>
        <em>"Devo pensarci"</em><br>
        <em>"Non ho tempo ora"</em></p>

        <p>Ma lascia che ti dica una cosa...</p>

        <div class="loss-list">
            <h3 style="margin-top: 0;">Cosa perderai se non agisci ORA:</h3>

            <p>❌ <strong>€240 di risparmio</strong> (differenza tra €7/mese e €27/mese)<br>
            ❌ <strong>Setup gratuito</strong> (valore €200)<br>
            ❌ <strong>Formazione 1-to-1</strong> (valore €150)<br>
            ❌ <strong>1° mese gratis</strong> (valore €27)</p>

            <p style="font-size: 20px; font-weight: bold; color: #dc2626; margin-top: 20px;">
                Totale perso: €617
            </p>
        </div>

        <p>Ma c'è di più...</p>

        <p><strong>Ogni settimana che aspetti, perdi:</strong></p>
        <ul>
            <li>⏰ 20 ore di tempo prezioso</li>
            <li>💰 €200-300 in pagamenti non riscossi</li>
            <li>😤 Stress e frustrazione quotidiana</li>
            <li>📉 Studenti che se ne vanno per disorganizzazione</li>
        </ul>

        <p style="font-size: 22px; font-weight: bold; text-align: center; background: #fee2e2; padding: 20px; border-radius: 8px; margin: 30px 0;">
            Aspettare ti costa più di quanto pensi
        </p>

        <p><strong>Pensa a cosa potrai fare con 20 ore in più ogni settimana:</strong></p>

        <ul>
            <li>✨ Creare nuovi corsi e aumentare le entrate</li>
            <li>✨ Passare più tempo con famiglia e amici</li>
            <li>✨ Fare ciò per cui hai aperto la scuola: <strong>ballare</strong></li>
        </ul>

        <p style="text-align: center; margin: 50px 0;">
            <a href="https://www.danzafacile.it" class="cta">⚡ ATTIVA ORA - ULTIMI POSTI</a>
        </p>

        <div style="background: #d1fae5; border-left: 4px solid #059669; padding: 20px; margin: 30px 0;">
            <strong>🎯 Setup in 3 semplici passaggi:</strong><br><br>

            <strong>Passo 1:</strong> Clicca sul bottone qui sopra (2 minuti)<br>
            <strong>Passo 2:</strong> Inserisci i dati della tua scuola (3 minuti)<br>
            <strong>Passo 3:</strong> Il nostro team ti chiama per il setup gratuito (15 minuti)<br><br>

            <strong>Totale:</strong> 20 minuti e sei operativo
        </div>

        <p><strong>Ricorda:</strong></p>

        <ul>
            <li>🛡️ Garanzia 30 giorni soddisfatti o rimborsati</li>
            <li>💳 Nessun vincolo - cancelli quando vuoi</li>
            <li>🎁 1° mese completamente GRATIS</li>
            <li>💰 Poi solo €7/mese (invece di €27/mese)</li>
        </ul>

        <p style="font-size: 24px; font-weight: bold; text-align: center; color: #dc2626; margin: 40px 0;">
            Non c'è letteralmente NESSUN RISCHIO
        </p>

        <p>L'unica cosa che puoi perdere è questa offerta.</p>

        <p>E scade tra poche ore.</p>

        <p style="text-align: center; margin: 50px 0;">
            <a href="https://www.danzafacile.it" class="cta">🔥 ATTIVA PRIMA CHE SIA TROPPO TARDI</a>
        </p>

        <p style="background: #fef3c7; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
            <strong>⚠️ ATTENZIONE:</strong> Restano solo <strong>3 posti</strong> disponibili questo mese.<br>
            Quando saranno finiti, dovrai aspettare il mese prossimo (al prezzo pieno).
        </p>

        <p style="margin-top: 50px;">{{Nome}}, è la tua scelta.</p>

        <p>Puoi continuare come hai sempre fatto, perdendo 20 ore a settimana in burocrazia...</p>

        <p><strong>OPPURE</strong></p>

        <p>Puoi decidere di cambiare, OGGI, e iniziare a recuperare il tuo tempo.</p>

        <p>Cosa scegli?</p>

        <p style="text-align: center; margin: 50px 0;">
            <a href="https://www.danzafacile.it" class="cta">✨ SÌ, SCELGO DI CAMBIARE ORA</a>
        </p>

        <p style="margin-top: 50px;">Ti aspetto,<br>
        <strong>Daniela Crescenzio</strong><br>
        Fondatrice DanzaFacile<br>
        📱 +39 340 929 5364</p>

        <p style="font-size: 12px; color: #999; margin-top: 30px;">P.S. Dopo stasera, questa offerta non sarà più disponibile. <strong>Mai più.</strong> Se hai ancora dubbi, chiamami al 340 929 5364. Sono qui per aiutarti.</p>

        <p style="font-size: 12px; color: #999;">P.P.S. Ricorda: non stai comprando un software. Stai comprando <strong>20 ore a settimana</strong> della tua vita. Quanto valgono?</p>
    </div>

    <div class="footer">
        <p>DanzaFacile - Il Software per Scuole di Danza<br>
        www.danzafacile.it | P.IVA 03003220732</p>
        <p style="margin-top: 10px; color: #dc2626; font-weight: bold;">
            ⏰ SCADENZA: Stasera a mezzanotte
        </p>
    </div>
</body>
</html>
HTML;
    }
}
