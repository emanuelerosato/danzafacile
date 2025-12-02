<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AdminHelpController
 *
 * Gestisce la sezione Aiuto/Guida per gli Admin delle scuole.
 * Fornisce documentazione dettagliata su tutte le funzionalità
 * della dashboard Admin con ricerca integrata.
 *
 * Features:
 * - Guida completa alle funzionalità Admin
 * - Ricerca testuale nella documentazione
 * - Navigazione rapida con ancore
 * - Contenuti espandibili/comprimibili
 * - Responsive design
 *
 * Sicurezza: Accessibile solo agli Admin tramite middleware 'role:admin'
 */
class AdminHelpController extends Controller
{
    /**
     * Mostra la pagina principale del sistema di aiuto
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $school = Auth::user()->school;

        // Configurazione delle sezioni della guida
        $helpSections = $this->getHelpSections();

        // Statistiche della scuola per contestualizzare la guida
        $schoolStats = $this->getSchoolStats($school);

        return view('admin.help.index', compact('helpSections', 'schoolStats', 'school'));
    }

    /**
     * Definisce le sezioni della guida con contenuti dettagliati
     *
     * @return array
     */
    private function getHelpSections()
    {
        return [
            'overview' => [
                'title' => '🏠 Dashboard Overview',
                'icon' => 'home',
                'priority' => 1,
                'description' => 'Panoramica generale della dashboard Admin e delle sue funzionalità principali.',
                'content' => [
                    'intro' => 'La Dashboard Admin è il centro di controllo della tua scuola di danza. Da qui puoi gestire corsi, studenti, pagamenti e tutte le attività operative in modo semplice e intuitivo.',
                    'key_features' => [
                        'Gestione completa corsi e calendario con orari personalizzati',
                        'Iscrizioni e gestione studenti con tracking presenze',
                        'Sistema pagamenti integrato (PayPal, contanti, bonifico)',
                        'Reports e analytics in tempo reale della scuola',
                        'Comunicazioni dirette con studenti e staff tramite ticket',
                        'Gestione eventi speciali (saggi, stage, competizioni)',
                        'Archivio documenti centralizzato e sicuro',
                        'Export dati in PDF ed Excel per report esterni'
                    ],
                    'workflow' => [
                        'Visualizza statistiche principali nella dashboard home (studenti attivi, corsi, pagamenti)',
                        'Naviga tra le sezioni usando la sidebar sinistra',
                        'Usa la ricerca globale per trovare rapidamente studenti o corsi',
                        'Controlla le notifiche per ticket urgenti o pagamenti scaduti',
                        'Consulta i report settimanali per monitorare l\'andamento',
                        'Configura le impostazioni scuola in "Gestione" → "Impostazioni"'
                    ],
                    'best_practices' => [
                        'Controlla la dashboard ogni mattina per verificare attività del giorno',
                        'Aggiorna regolarmente i dati studenti e corsi',
                        'Rispondi ai ticket studenti entro 24 ore',
                        'Fai backup mensili dei dati esportando i report',
                        'Forma il tuo staff sulle procedure operative standard',
                        'Mantieni aggiornate le configurazioni PayPal e fiscali'
                    ],
                    'security_tips' => [
                        'Cambia password ogni 3 mesi',
                        'Non condividere credenziali con persone non autorizzate',
                        'Verifica sempre le transazioni PayPal prima di confermare',
                        'Fai logout quando lasci il computer incustodito',
                        'Controlla regolarmente i log di accesso per attività sospette'
                    ]
                ]
            ],

            'courses' => [
                'title' => '📚 Gestione Corsi',
                'icon' => 'academic-cap',
                'priority' => 2,
                'description' => 'Come creare e gestire corsi, orari, sale e programmi didattici.',
                'content' => [
                    'intro' => 'La sezione Corsi ti permette di gestire tutta l\'offerta formativa della scuola, dagli orari alle sale, dai livelli agli istruttori.',
                    'operations' => [
                        'create' => [
                            'title' => '➕ Creare un Nuovo Corso',
                            'steps' => [
                                'Vai su "Gestione Corsi" → "Corsi" → bottone "Nuovo Corso"',
                                'Compila nome corso (es. "Danza Classica - Principianti")',
                                'Scrivi descrizione dettagliata e obiettivi didattici',
                                'Seleziona livello (Principiante, Intermedio, Avanzato, Professionale)',
                                'Assegna istruttore dal menu a tendina',
                                'Configura orari settimanali (giorni, ora inizio/fine, sala)',
                                'Imposta prezzo mensile/trimestrale/annuale',
                                'Definisci numero massimo partecipanti',
                                'Aggiungi eventualmente attrezzature richieste',
                                'Attiva il corso con il toggle "Corso Attivo"',
                                'Salva e pubblica per renderlo visibile agli studenti'
                            ],
                            'tips' => 'Verifica sempre disponibilità sale e istruttori prima di creare un corso. Usa nomi descrittivi che includano stile e livello.'
                        ],
                        'manage' => [
                            'title' => '✏️ Gestire Corsi Esistenti',
                            'features' => [
                                'Visualizzazione calendario settimanale con tutti i corsi',
                                'Modifica orari, sale e istruttori con pochi click',
                                'Aggiunta/rimozione studenti iscritti',
                                'Gestione lista d\'attesa quando corso pieno',
                                'Monitoraggio posti disponibili in tempo reale',
                                'Attivazione/Disattivazione rapida dei corsi',
                                'Duplicazione corsi per nuove stagioni (copia tutti i dettagli)',
                                'Export lista iscritti in PDF o Excel',
                                'Statistiche frequenza e presenze per corso'
                            ],
                            'tips' => 'Usa la funzione "Duplica Corso" per risparmiare tempo quando apri una nuova stagione.'
                        ],
                        'rooms' => [
                            'title' => '🏛️ Gestione Sale',
                            'steps' => [
                                'Vai su "Gestione Corsi" → "Gestisci Sale"',
                                'Clicca "Nuova Sala" per aggiungere una sala',
                                'Inserisci nome sala (es. "Sala Specchi Grande")',
                                'Definisci capienza massima studenti',
                                'Aggiungi attrezzature disponibili (specchi, sbarre, audio)',
                                'Salva e assegna ai corsi'
                            ],
                            'features' => [
                                'Visualizzazione occupazione sale in tempo reale',
                                'Alert automatici per conflitti orari',
                                'Calendario prenotazioni sala',
                                'Statistiche utilizzo sale'
                            ],
                            'tips' => 'Mantieni sempre una sala di backup per sostituzioni o emergenze.'
                        ],
                        'schedules' => [
                            'title' => '📅 Pianificazione Orari',
                            'features' => [
                                'Drag & drop per spostare lezioni nel calendario',
                                'Verifica automatica conflitti orari',
                                'Template orari per stagioni ricorrenti',
                                'Export calendario in formato PDF/iCal'
                            ],
                            'tips' => 'Pianifica gli orari considerando fasce orarie preferite dagli studenti (pomeriggio/sera).'
                        ]
                    ],
                    'best_practices' => [
                        'Mantieni i nomi corsi chiari e descrittivi (stile + livello)',
                        'Aggiorna regolarmente le descrizioni con gli obiettivi stagionali',
                        'Verifica settimanalmente i posti disponibili e liste d\'attesa',
                        'Comunica modifiche orari con almeno 1 settimana di anticipo',
                        'Archivia corsi conclusi invece di eliminarli (per storico)',
                        'Usa tag o categorie per organizzare l\'offerta formativa'
                    ]
                ]
            ],

            'students' => [
                'title' => '👥 Gestione Studenti',
                'icon' => 'users',
                'priority' => 3,
                'description' => 'Iscrizioni, presenze, documenti e comunicazioni con gli studenti.',
                'content' => [
                    'intro' => 'Gestisci tutti gli aspetti relativi agli studenti della scuola: dalle iscrizioni alle presenze, dai documenti alle comunicazioni.',
                    'operations' => [
                        'enrollments' => [
                            'title' => '📝 Iscrizioni',
                            'steps' => [
                                'Vai su "Studenti" → "Iscrizioni"',
                                'Visualizza tutte le iscrizioni (attive, in attesa, cancellate)',
                                'Per nuova iscrizione: seleziona studente e corso',
                                'Scegli tipo pagamento (unica soluzione/rate)',
                                'Genera pagamento associato all\'iscrizione',
                                'Conferma iscrizione e invia email notifica allo studente'
                            ],
                            'features' => [
                                'Iscrizione manuale studenti ai corsi',
                                'Approvazione automatica/manuale richieste online',
                                'Gestione liste d\'attesa con notifiche automatiche',
                                'Trasferimenti rapidi tra corsi dello stesso livello',
                                'Cancellazioni con gestione rimborsi proporzionali',
                                'Rinnovi automatici per stagioni successive',
                                'Export elenco iscritti per corso'
                            ],
                            'tips' => 'Imposta liste d\'attesa per corsi popolari e notifica automaticamente quando si liberano posti.'
                        ],
                        'attendance' => [
                            'title' => '✅ Presenze',
                            'steps' => [
                                'Vai su "Studenti" → "Presenze"',
                                'Seleziona corso e data lezione',
                                'Spunta presente/assente per ogni studente',
                                'Aggiungi note se necessario (es. "infortunio", "giustificato")',
                                'Salva presenze (invio automatico notifiche ai genitori)'
                            ],
                            'features' => [
                                'Registrazione presenze/assenze con un click',
                                'Report presenze dettagliato per studente',
                                'Statistiche frequenza per corso (% presenza media)',
                                'Alert automatici per assenze ripetute (3+ consecutive)',
                                'Calendario presenze studente con storico completo',
                                'Export registro presenze mensile in PDF',
                                'Registrazione presenze bulk (tutti presenti/assenti)'
                            ],
                            'tips' => 'Registra le presenze subito dopo la lezione per non dimenticare. Usa le note per tracciare motivi assenze importanti.'
                        ],
                        'documents' => [
                            'title' => '📄 Documenti',
                            'steps' => [
                                'Vai su "Studenti" → "Documenti"',
                                'Filtra per tipo documento o studente',
                                'Controlla stato (valido, scaduto, in attesa)',
                                'Approva/rifiuta documenti caricati dagli studenti',
                                'Carica manualmente documenti per studenti',
                                'Scarica o visualizza documento con anteprima'
                            ],
                            'features' => [
                                'Archivio centralizzato documenti studenti',
                                'Controllo scadenze certificati medici',
                                'Notifiche automatiche documenti in scadenza',
                                'Approvazione/rifiuto rapido documenti',
                                'Download massivo documenti per categoria',
                                'Ricerca avanzata per studente/tipo/stato'
                            ],
                            'tips' => 'Imposta reminder automatici 30 giorni prima scadenza certificati medici per evitare problemi assicurativi.'
                        ],
                        'profiles' => [
                            'title' => '👤 Profili Studenti',
                            'features' => [
                                'Dati anagrafici completi (nome, cognome, data nascita)',
                                'Contatti studente e genitori (email, telefono)',
                                'Storico iscrizioni e corsi frequentati',
                                'Statistiche presenze e performance',
                                'Note interne riservate admin',
                                'Upload foto profilo',
                                'Gestione account e credenziali accesso'
                            ],
                            'tips' => 'Mantieni sempre aggiornati i contatti di emergenza dei genitori.'
                        ]
                    ],
                    'best_practices' => [
                        'Verifica certificati medici prima di confermare iscrizioni',
                        'Registra presenze immediatamente dopo ogni lezione',
                        'Rispondi entro 24h alle richieste di iscrizione online',
                        'Mantieni privacy dati studenti (GDPR compliance)',
                        'Comunica con genitori per assenze prolungate (3+ giorni)',
                        'Archivia documenti studenti per almeno 5 anni'
                    ]
                ]
            ],

            'payments' => [
                'title' => '💳 Pagamenti',
                'icon' => 'credit-card',
                'priority' => 4,
                'description' => 'Gestione pagamenti, ricevute, rimborsi e fatturazione.',
                'content' => [
                    'intro' => 'Sistema completo per gestire tutti i pagamenti della scuola: dalle iscrizioni ai saggi, con ricevute automatiche e tracciamento completo.',
                    'operations' => [
                        'create_payment' => [
                            'title' => '💰 Creare un Pagamento',
                            'steps' => [
                                'Vai su "Gestione" → "Pagamenti" → "Nuovo Pagamento"',
                                'Seleziona studente dal menu a tendina',
                                'Scegli tipo (Iscrizione, Saggio, Stage, Altro)',
                                'Inserisci importo e descrizione dettagliata',
                                'Seleziona metodo pagamento (PayPal, Contanti, Bonifico)',
                                'Imposta scadenza se pagamento dilazionato',
                                'Salva e genera ricevuta automatica',
                                'Invia ricevuta via email allo studente'
                            ],
                            'tips' => 'Per pagamenti rateali, crea più pagamenti con scadenze diverse invece di un unico importo.'
                        ],
                        'process_payment' => [
                            'title' => '✅ Processare Pagamenti',
                            'features' => [
                                'Visualizzazione stato pagamenti (Pagato, In attesa, Scaduto)',
                                'Marcatura manuale come "Completato" per contanti/bonifici',
                                'Invio reminder automatici per pagamenti in scadenza',
                                'Riconciliazione transazioni PayPal automatica',
                                'Generazione ricevuta immediata al pagamento',
                                'Invio email conferma pagamento allo studente',
                                'Dashboard pagamenti in tempo reale con filtri'
                            ],
                            'tips' => 'Controlla giornalmente i pagamenti PayPal per riconciliare le transazioni online.'
                        ],
                        'refunds' => [
                            'title' => '↩️ Rimborsi',
                            'steps' => [
                                'Apri pagamento da rimborsare',
                                'Clicca "Rimborsa"',
                                'Seleziona tipo: Totale o Parziale',
                                'Inserisci importo rimborso e motivazione',
                                'Conferma rimborso (automatico su PayPal, manuale per altri metodi)',
                                'Sistema genera nota di credito automatica',
                                'Invia conferma rimborso via email'
                            ],
                            'tips' => 'I rimborsi PayPal sono immediati. Per bonifici serve coordinamento con banca (3-5 giorni lavorativi).'
                        ],
                        'receipts' => [
                            'title' => '🧾 Ricevute',
                            'features' => [
                                'Generazione automatica al completamento pagamento',
                                'Template personalizzabile con logo scuola',
                                'Dati fiscali completi (P.IVA, Codice Fiscale)',
                                'Numerazione progressiva annuale',
                                'Download PDF con un click',
                                'Invio automatico via email',
                                'Ristampa ricevute archiviate'
                            ],
                            'tips' => 'Configura intestazione ricevute in "Impostazioni" prima di emettere la prima ricevuta.'
                        ]
                    ],
                    'best_practices' => [
                        'Riconcilia pagamenti PayPal quotidianamente',
                        'Invia reminder 7 giorni prima scadenza pagamenti',
                        'Archivia ricevute digitali per almeno 10 anni (obbligo fiscale)',
                        'Verifica sempre identità studente prima rimborsi in contanti',
                        'Esporta report mensili per commercialista',
                        'Mantieni separati pagamenti corsi da eventi speciali'
                    ],
                    'security_tips' => [
                        'Verifica sempre transazioni PayPal prima di confermare iscrizioni',
                        'Non memorizzare dati carte di credito nel sistema',
                        'Richiedi autorizzazione scritta per rimborsi > €100',
                        'Fai backup settimanale database pagamenti',
                        'Controlla report anomalie per possibili frodi'
                    ]
                ]
            ],

            'events' => [
                'title' => '🎭 Eventi',
                'icon' => 'calendar',
                'priority' => 5,
                'description' => 'Organizza saggi, competizioni e eventi speciali.',
                'content' => [
                    'intro' => 'Crea e gestisci eventi speciali oltre ai corsi regolari: saggi, stage, competizioni e occasioni speciali per la tua scuola.',
                    'key_features' => [
                        'Saggi di fine anno con gestione completa partecipanti',
                        'Stage con maestri ospiti nazionali/internazionali',
                        'Competizioni e gare',
                        'Open day e lezioni prova gratuite',
                        'Feste a tema e serate danzanti',
                        'Masterclass tematiche',
                        'Spettacoli e performance pubbliche'
                    ],
                    'operations' => [
                        'create' => [
                            'title' => '📅 Creare un Evento',
                            'steps' => [
                                'Vai su "Eventi" → "Lista Eventi" → "Nuovo Evento"',
                                'Inserisci titolo accattivante (es. "Saggio di Natale 2024")',
                                'Seleziona tipo evento dal menu',
                                'Imposta data, ora inizio e fine',
                                'Definisci location (sede scuola o esterna)',
                                'Scrivi descrizione dettagliata con programma',
                                'Carica locandina/immagine evento (formato JPG/PNG)',
                                'Imposta prezzo partecipazione (se a pagamento)',
                                'Definisci numero massimo partecipanti',
                                'Scegli se attivare registrazioni online',
                                'Pubblica evento per renderlo visibile agli studenti'
                            ],
                            'tips' => 'Crea eventi almeno 30 giorni prima per dare tempo alle iscrizioni. Usa immagini di alta qualità per la locandina.'
                        ],
                        'registrations' => [
                            'title' => '👥 Gestione Registrazioni',
                            'steps' => [
                                'Vai su "Eventi" → "Registrazioni"',
                                'Filtra per evento specifico',
                                'Visualizza lista partecipanti registrati',
                                'Approva/rifiuta registrazioni manuali se necessario',
                                'Invia comunicazioni di gruppo ai partecipanti',
                                'Marca presenze il giorno dell\'evento',
                                'Export lista partecipanti in PDF/Excel'
                            ],
                            'features' => [
                                'Elenco partecipanti con stato (Confermato, In attesa, Cancellato)',
                                'Conferme presenza pre-evento',
                                'Email di massa ai partecipanti',
                                'Registro presenze evento',
                                'Gestione lista d\'attesa per eventi sold-out',
                                'Statistiche partecipazione per analisi'
                            ],
                            'tips' => 'Invia reminder 3 giorni prima dell\'evento per confermare presenza e ridurre no-show.'
                        ],
                        'payments' => [
                            'title' => '💳 Pagamenti Eventi',
                            'features' => [
                                'Generazione automatica pagamento all\'iscrizione',
                                'Pagamenti online tramite PayPal',
                                'Pagamenti in sede (contanti/POS)',
                                'Ricevute automatiche partecipazione',
                                'Report incassi per evento'
                            ],
                            'tips' => 'Per eventi gratuiti, disabilita richiesta pagamento ma mantieni registrazione per tracciare partecipanti.'
                        ]
                    ],
                    'best_practices' => [
                        'Pianifica saggi con almeno 2 mesi anticipo',
                        'Comunica dettagli evento (orari, dress code) almeno 1 settimana prima',
                        'Fai prove generali 2-3 giorni prima del saggio',
                        'Prepara elenchi partecipanti stampati come backup',
                        'Scatta foto/video per galleria ricordi (con consenso genitori)',
                        'Raccogli feedback post-evento per migliorare organizzazione'
                    ]
                ]
            ],

            'public_events' => [
                'title' => '🌐 Eventi Pubblici',
                'icon' => 'globe',
                'priority' => 6,
                'description' => 'Crea landing page pubbliche per eventi aperti a tutti con iscrizioni online.',
                'content' => [
                    'intro' => 'Gli Eventi Pubblici permettono di creare landing page pubbliche per eventi aperti a chiunque, non solo agli studenti iscritti. Sistema completo con iscrizioni guest, pagamenti PayPal e email marketing automatico.',
                    'key_features' => [
                        'Landing page pubblica accessibile senza login',
                        'Iscrizioni guest con dati minimi (nome, email, telefono)',
                        'Magic link per accesso sicuro senza password',
                        'Pagamenti PayPal integrati con ricevute automatiche',
                        'Email funnel automatico (conferma + reminder + thank you)',
                        'Gestione GDPR completa con consensi privacy',
                        'Dashboard admin per monitorare iscrizioni e incassi',
                        'Statistiche real-time partecipanti e pagamenti'
                    ],
                    'operations' => [
                        'create_public_event' => [
                            'title' => '🎉 Creare un Evento Pubblico',
                            'steps' => [
                                'Vai su "Eventi" → "Lista Eventi" → "Nuovo Evento"',
                                'Compila i campi standard (titolo, data, descrizione, locandina)',
                                'IMPORTANTE: Attiva il toggle "Evento Pubblico" (è pubblico = ON)',
                                'Imposta lo slug URL personalizzato (es. "stage-estate-2024")',
                                'Configura prezzo (può essere gratuito o a pagamento)',
                                'Definisci numero massimo partecipanti (opzionale)',
                                'Scrivi descrizione accattivante per la landing page',
                                'Carica immagine evento di alta qualità (min 1200x630px)',
                                'Salva e pubblica evento',
                                'Sistema genera automaticamente landing page su: www.tuascuola.it/eventi/[slug]'
                            ],
                            'tips' => 'Usa slug brevi e memorabili. La landing page è ottimizzata SEO per Google. Testa sempre il link prima di condividerlo.'
                        ],
                        'landing_page' => [
                            'title' => '🌐 Landing Page Pubblica',
                            'features' => [
                                'URL pubblico senza login richiesto (SEO friendly)',
                                'Design responsive mobile-first',
                                'Countdown automatico alla data evento',
                                'Form iscrizione semplificato (solo dati essenziali)',
                                'Protezione reCAPTCHA anti-spam integrata',
                                'Posti disponibili in tempo reale',
                                'Pulsante "Iscriviti Ora" call-to-action prominente',
                                'Social sharing buttons (Facebook, WhatsApp, Instagram)'
                            ],
                            'tips' => 'Condividi link su social e newsletter. Monitora analytics per vedere quante visite ricevi.'
                        ],
                        'guest_registration' => [
                            'title' => '👤 Registrazioni Guest',
                            'steps' => [
                                'Guest compila form pubblico (nome, email, telefono)',
                                'Accetta privacy policy (GDPR obbligatorio)',
                                'Può opzionalmente accettare marketing/newsletter',
                                'Sistema invia email con magic link sicuro',
                                'Guest clicca magic link per accedere (no password)',
                                'Se evento a pagamento → redirect a pagina PayPal',
                                'Se evento gratuito → conferma iscrizione immediata',
                                'Admin riceve notifica nuova registrazione'
                            ],
                            'features' => [
                                'Account guest automatico (no registrazione complessa)',
                                'Magic link valido 7 giorni per accesso sicuro',
                                'Gestione consensi GDPR separati (privacy, marketing, newsletter)',
                                'Rate limiting anti-spam (max 3 tentativi/10min per IP)',
                                'Verifica email automatica',
                                'Cleanup automatico guest inattivi dopo 180 giorni',
                                'Lista partecipanti in admin dashboard'
                            ],
                            'tips' => 'Magic link evita password deboli. Guest possono iscriversi a più eventi con stessa email.'
                        ],
                        'payment_flow' => [
                            'title' => '💰 Flusso Pagamenti',
                            'steps' => [
                                'Guest completa iscrizione → Sistema crea payment record',
                                'Guest reindirizzato a pagina pagamento PayPal',
                                'Guest completa pagamento su PayPal',
                                'PayPal invia webhook conferma transazione',
                                'Sistema marca pagamento come "Completato"',
                                'Iscrizione passa da "pending_payment" a "confirmed"',
                                'Email conferma pagamento inviata automaticamente',
                                'Ricevuta PDF generata e allegata all\'email',
                                'Admin può vedere incassi in dashboard eventi'
                            ],
                            'features' => [
                                'Integrazione PayPal completa con webhook',
                                'Ricevute automatiche PDF con logo scuola',
                                'Gestione rimborsi tramite PayPal API',
                                'Dashboard incassi per evento',
                                'Report pagamenti esportabile in Excel',
                                'Supporto eventi gratuiti (no pagamento richiesto)',
                                'Transazioni sicure PCI-compliant'
                            ],
                            'tips' => 'Per eventi gratuiti imposta prezzo a €0. PayPal non viene chiamato e iscrizione è immediata.'
                        ],
                        'email_funnel' => [
                            'title' => '📧 Email Marketing Automatico',
                            'steps' => [
                                'Email 1: Conferma Registrazione (immediata dopo iscrizione)',
                                '  ↳ Contiene: Magic link, dettagli evento, link calendario (.ics)',
                                'Email 2: Reminder 7 Giorni Prima (scheduled automatico)',
                                '  ↳ Contiene: Countdown, indicazioni arrivo, cosa portare',
                                'Email 3: Reminder 1 Giorno Prima (scheduled automatico)',
                                '  ↳ Contiene: Last chance reminder, mappa location, contatti emergenza',
                                'Email 4: Thank You Post-Evento (giorno dopo)',
                                '  ↳ Contiene: Ringraziamento, survey feedback, prossimi eventi'
                            ],
                            'features' => [
                                'Template email professionali responsive',
                                'Scheduling automatico basato su data evento',
                                'Personalizzazione con dati partecipante',
                                'Link magic login per accesso rapido',
                                'Allegato file .ics per Google Calendar/Outlook',
                                'Tracking aperture email (analytics)',
                                'Unsubscribe link GDPR compliant'
                            ],
                            'tips' => 'Le email reminder riducono no-show del 40%. Personalizza template in "Impostazioni" → "Email".'
                        ],
                        'admin_management' => [
                            'title' => '⚙️ Gestione Admin',
                            'steps' => [
                                'Vai su "Eventi" → "Lista Eventi" → Seleziona evento pubblico',
                                'Tab "Registrazioni" → Visualizza tutti gli iscritti',
                                'Filtra per stato (confermato, in attesa pagamento, cancellato)',
                                'Approva/rifiuta manualmente registrazioni se necessario',
                                'Marca presenze il giorno dell\'evento',
                                'Visualizza statistiche in tempo reale (iscritti, incassi, posti)',
                                'Export lista partecipanti in PDF/Excel',
                                'Invia email broadcast a tutti i partecipanti'
                            ],
                            'features' => [
                                'Dashboard completa con KPI evento',
                                'Lista partecipanti filtrabilecon ricerca',
                                'Statistiche real-time (iscritti/max, incasso totale, % riempimento)',
                                'Gestione waitlist quando sold-out',
                                'Invio email massive ai partecipanti',
                                'Export dati per analytics esterne',
                                'Registro presenze digitale'
                            ],
                            'tips' => 'Monitora % riempimento quotidianamente. Se basso < 50% una settimana prima, intensifica marketing.'
                        ]
                    ],
                    'best_practices' => [
                        'Crea evento almeno 30 giorni prima per dare tempo iscrizioni',
                        'Usa immagini professionali alta risoluzione per landing page',
                        'Scrivi descrizione evento chiara con programma dettagliato',
                        'Imposta numero max partecipanti per creare urgency',
                        'Condividi link evento su social e newsletter scuola',
                        'Monitora iscrizioni quotidianamente e adatta strategie',
                        'Rispondi entro 24h a richieste info via email/ticket',
                        'Fai follow-up post-evento per raccogliere feedback',
                        'Analizza statistiche per migliorare eventi futuri',
                        'Mantieni database guest aggiornato per future campagne'
                    ],
                    'security_tips' => [
                        'Magic link scadono dopo 7 giorni per sicurezza',
                        'reCAPTCHA protegge da bot spam automatici',
                        'Rate limiting previene abusi iscrizioni multiple',
                        'Tutti i consensi GDPR sono tracciati e archiviati',
                        'Guest inattivi sono eliminati automaticamente dopo 180 giorni',
                        'Transazioni PayPal usano protocollo HTTPS sicuro',
                        'Webhook PayPal verificati con firma digitale',
                        'Dati sensibili guest criptati in database'
                    ],
                    'gdpr_compliance' => [
                        'Privacy Policy obbligatoria al momento iscrizione',
                        'Consensi separati per marketing e newsletter (opt-in)',
                        'Right to be forgotten: guest può richiedere cancellazione dati',
                        'Data retention policy: 180 giorni inattività',
                        'Email hanno sempre link unsubscribe',
                        'Log audit completo per tutte le operazioni GDPR',
                        'Dati personali accessibili solo ad admin autorizzati'
                    ],
                    'troubleshooting' => [
                        'Evento non visibile pubblicamente → Verifica toggle "È Pubblico" attivo',
                        'Magic link non funziona → Link scade dopo 7 giorni, genera nuovo',
                        'Pagamento PayPal fallisce → Verifica config PayPal in Impostazioni',
                        'Email non arrivano → Controlla spam, verifica SMTP configurato',
                        'Posti esauriti troppo presto → Aumenta max partecipanti o crea replica evento',
                        'Registrazioni spam → reCAPTCHA già attivo, aumenta difficoltà se necessario',
                        'Guest duplicati → Sistema previene, ma controlla email diverse (typo)'
                    ]
                ]
            ],

            'staff' => [
                'title' => '👨‍🏫 Staff & Istruttori',
                'icon' => 'user-group',
                'priority' => 7,
                'description' => 'Gestione team, orari, turni e paghe collaboratori.',
                'content' => [
                    'intro' => 'Organizza e coordina il team di istruttori e staff della scuola: gestisci profili, orari, sostituzioni e compensi.',
                    'operations' => [
                        'profiles' => [
                            'title' => '👤 Profili Staff',
                            'steps' => [
                                'Vai su "Staff" → "Gestione Staff" → "Nuovo Staff"',
                                'Inserisci dati anagrafici completi',
                                'Aggiungi contatti (email, telefono, indirizzo)',
                                'Specifica ruolo (Istruttore, Assistente, Segreteria)',
                                'Elenca specializzazioni e discipline insegnate',
                                'Carica CV e certificazioni/diplomi',
                                'Imposta disponibilità oraria settimanale',
                                'Definisci tariffa oraria/mensile',
                                'Crea account accesso se necessario',
                                'Salva profilo e assegna ai corsi'
                            ],
                            'features' => [
                                'Dati anagrafici e fiscali completi',
                                'Specializzazioni e qualifiche professionali',
                                'Disponibilità oraria configurabile',
                                'Storico completo corsi insegnati',
                                'Upload documenti (CV, diplomi, certificati)',
                                'Note interne riservate admin',
                                'Statistiche performance e valutazioni'
                            ],
                            'tips' => 'Mantieni aggiornati CV e certificazioni per verifiche ispettive o richieste genitori.'
                        ],
                        'schedules' => [
                            'title' => '📅 Orari e Turni',
                            'steps' => [
                                'Vai su "Staff" → "Orari & Turni"',
                                'Seleziona settimana/mese da visualizzare',
                                'Visualizza calendario completo turni staff',
                                'Per nuova assegnazione: seleziona staff + corso + orario',
                                'Verifica automatica disponibilità e conflitti',
                                'Salva assegnazione turno',
                                'Invia notifica email istruttore'
                            ],
                            'features' => [
                                'Calendario turni personale visualizzazione settimanale/mensile',
                                'Gestione sostituzioni rapide con notifiche',
                                'Alert automatici conflitti orari',
                                'Tracking ore lavorate in tempo reale',
                                'Export orari personali in PDF',
                                'Dashboard disponibilità staff',
                                'Pianificazione turni festivi'
                            ],
                            'tips' => 'Pianifica sostituzioni con almeno 24h anticipo. Mantieni lista staff disponibili per emergenze.'
                        ],
                        'payroll' => [
                            'title' => '💰 Gestione Paghe',
                            'steps' => [
                                'Vai su "Staff" → "Gestione Paghe"',
                                'Seleziona mese di riferimento',
                                'Sistema calcola automaticamente ore lavorate per staff',
                                'Verifica ore e applica eventuali correzioni',
                                'Genera report compensi con dettagli',
                                'Export report in Excel per elaborazione paghe',
                                'Marca pagamenti come "Processati" dopo erogazione'
                            ],
                            'features' => [
                                'Calcolo automatico ore lavorate per periodo',
                                'Applicazione tariffe orarie personalizzate',
                                'Gestione straordinari e festività',
                                'Report compensi mensili dettagliati',
                                'Export dati per commercialista/consulente lavoro',
                                'Storico pagamenti staff',
                                'Dashboard costi personale'
                            ],
                            'tips' => 'Chiudi il mese entro il giorno 5 successivo per pagamenti puntuali. Verifica sempre ore prima export finale.'
                        ]
                    ],
                    'best_practices' => [
                        'Verifica documenti staff prima assunzione (certificazioni, diplomi)',
                        'Mantieni backup di emergenza per sostituzioni improvvise',
                        'Comunica modifiche turni con almeno 48h anticipo',
                        'Fai riunioni staff mensili per coordinamento',
                        'Raccogli feedback studenti su istruttori periodicamente',
                        'Archivia presenze staff per almeno 5 anni'
                    ]
                ]
            ],

            'reports' => [
                'title' => '📊 Reports & Analytics',
                'icon' => 'chart-bar',
                'priority' => 8,
                'description' => 'Analytics, KPI e report operativi della scuola.',
                'content' => [
                    'intro' => 'Monitora le performance della scuola con report dettagliati e analytics in tempo reale per decisioni data-driven.',
                    'operations' => [
                        'financial' => [
                            'title' => '💰 Report Finanziari',
                            'steps' => [
                                'Vai su "Analytics" → "Reports & Analytics"',
                                'Seleziona tab "Finanziari"',
                                'Imposta periodo analisi (mese, trimestre, anno)',
                                'Visualizza dashboard con grafici fatturato',
                                'Analizza incassi per fonte (corsi, eventi, altro)',
                                'Controlla pagamenti in sospeso e scaduti',
                                'Export report in PDF/Excel per commercialista'
                            ],
                            'features' => [
                                'Fatturato mensile/trimestrale/annuale con grafici trend',
                                'Breakdown incassi per corso/evento',
                                'Pagamenti in sospeso con alert scadenze',
                                'Trend crescita ricavi year-over-year',
                                'Previsioni incassi mensili',
                                'Report fiscali per dichiarazioni',
                                'Confronto budget vs actual'
                            ],
                            'tips' => 'Esporta report mensili per commercialista entro il giorno 10 del mese successivo.'
                        ],
                        'students' => [
                            'title' => '👥 Report Studenti',
                            'steps' => [
                                'Vai su "Analytics" → "Reports & Analytics"',
                                'Seleziona tab "Studenti"',
                                'Visualizza KPI principali (totali, nuovi, attivi)',
                                'Analizza retention rate periodo su periodo',
                                'Controlla presenze medie per corso',
                                'Identifica trend dropout (studenti che abbandonano)',
                                'Export lista completa studenti con statistiche'
                            ],
                            'features' => [
                                'Nuove iscrizioni per periodo con trend',
                                'Retention rate (% studenti che rinnovano)',
                                'Presenze medie per corso (% frequenza)',
                                'Dropout analysis (motivi abbandono)',
                                'Distribuzione geografica studenti',
                                'Età media per livello/corso',
                                'Crescita base studenti mensile'
                            ],
                            'tips' => 'Monitora retention rate mensile: <80% richiede azioni immediate per migliorare soddisfazione.'
                        ],
                        'courses' => [
                            'title' => '📚 Report Corsi',
                            'steps' => [
                                'Vai su "Analytics" → "Reports & Analytics"',
                                'Seleziona tab "Corsi"',
                                'Visualizza ranking corsi per popolarità',
                                'Analizza tasso riempimento sale',
                                'Controlla performance istruttori (presenze, satisfaction)',
                                'Identifica corsi sotto-performanti',
                                'Export report completo corsi in Excel'
                            ],
                            'features' => [
                                'Ranking corsi più popolari (iscritti/lista attesa)',
                                'Tasso occupazione sale (utilizzo efficiente spazi)',
                                'Performance istruttori (studenti gestiti, soddisfazione)',
                                'Indice soddisfazione studenti per corso',
                                'Analisi fasce orarie più richieste',
                                'Corsi con margine profitto migliore',
                                'Confronto corsi simili'
                            ],
                            'tips' => 'Corsi con <60% riempimento vanno valutati per chiusura o ri-pianificazione orari.'
                        ]
                    ],
                    'best_practices' => [
                        'Consulta dashboard almeno settimanalmente',
                        'Esporta report mensili per archivio storico',
                        'Usa analytics per pianificare offerta formativa stagione',
                        'Condividi KPI principali con staff in riunioni',
                        'Imposta alert automatici per KPI critici (retention, presenze)',
                        'Confronta sempre performance anno su anno per trend'
                    ]
                ]
            ],

            'tickets' => [
                'title' => '🎫 Sistema Ticket',
                'icon' => 'chat',
                'priority' => 9,
                'description' => 'Gestisci richieste supporto e comunicazioni studenti.',
                'content' => [
                    'intro' => 'Centralizza tutte le richieste di supporto degli studenti con sistema ticket professionale: traccia, rispondi e risolvi ogni richiesta efficacemente.',
                    'operations' => [
                        'manage_tickets' => [
                            'title' => '📬 Gestione Ticket',
                            'steps' => [
                                'Vai su "Supporto" → "Ticket"',
                                'Visualizza lista ticket (tutti, aperti, in attesa, chiusi)',
                                'Filtra per stato, priorità, categoria o studente',
                                'Clicca su ticket per aprire dettaglio',
                                'Leggi messaggio studente e storico conversazione',
                                'Scrivi risposta nel campo testo',
                                'Cambia stato/priorità se necessario',
                                'Invia risposta (notifica automatica allo studente)',
                                'Chiudi ticket quando problema risolto'
                            ],
                            'features' => [
                                'Dashboard completa con filtri avanzati',
                                'Visualizzazione stato real-time (aperto, in attesa, chiuso)',
                                'Storico completo conversazione per ticket',
                                'Notifiche email automatiche a studente',
                                'Assegnazione ticket a staff specifico',
                                'Ricerca full-text in messaggi',
                                'Statistics dashboard ticket (tempo medio risoluzione)'
                            ],
                            'tips' => 'Rispondi ai ticket entro 24h per mantenere alta soddisfazione studenti. Usa template risposte per FAQ.'
                        ],
                        'priorities' => [
                            'title' => '⚡ Gestione Priorità',
                            'features' => [
                                'Bassa (verde) - Informazioni generali, FAQ (risposta entro 48h)',
                                'Media (gialla) - Richieste amministrative, documenti (risposta entro 24h)',
                                'Alta (arancione) - Problemi pagamenti, iscrizioni urgenti (risposta entro 12h)',
                                'Critica (rossa) - Emergenze, problemi gravi (risposta immediata)'
                            ],
                            'tips' => 'Studenti possono assegnare priorità ma admin può modificarla. Monitora ticket critici quotidianamente.'
                        ],
                        'categories' => [
                            'title' => '📂 Categorie Ticket',
                            'features' => [
                                'Problemi Tecnici - Login, password, accesso piattaforma',
                                'Pagamenti - Ricevute, rimborsi, transazioni PayPal',
                                'Corsi/Lezioni - Orari, sale, istruttori, iscrizioni',
                                'Account/Profilo - Dati anagrafici, documenti, certificati',
                                'Informazioni Generali - Domande generiche sulla scuola',
                                'Altro - Richieste non classificabili'
                            ],
                            'tips' => 'Usa categorie per assegnare automaticamente ticket allo staff competente.'
                        ],
                        'bulk_actions' => [
                            'title' => '⚙️ Azioni Multiple',
                            'steps' => [
                                'Seleziona checkbox su più ticket nella lista',
                                'Clicca "Azioni Multiple" in alto',
                                'Scegli azione: Chiudi, Assegna a Staff, Cambia Priorità',
                                'Se assegnazione: seleziona staff dal menu',
                                'Conferma azione bulk',
                                'Sistema processa tutti i ticket selezionati'
                            ],
                            'features' => [
                                'Chiusura multipla ticket risolti',
                                'Assegnazione bulk a staff specifico',
                                'Cambio priorità massivo',
                                'Riapertura ticket chiusi per errore'
                            ],
                            'tips' => 'Usa bulk close per chiudere rapidamente ticket spam o duplicati. Verifica sempre selezione prima conferma.'
                        ]
                    ],
                    'best_practices' => [
                        'SLA target: 80% ticket risolti entro 24h',
                        'Rispondi sempre con tono professionale e cortese',
                        'Chiedi conferma risoluzione prima chiudere ticket',
                        'Usa template per risposte FAQ frequenti',
                        'Assegna ticket a staff competente per materia',
                        'Monitora satisfaction rate ticket chiusi'
                    ],
                    'workflow' => [
                        'Studente crea ticket → Stato "Aperto" + Notifica admin',
                        'Admin legge e risponde → Stato "In Attesa" + Notifica studente',
                        'Studente risponde → Stato torna "Aperto"',
                        'Admin risolve problema → Chiede conferma risoluzione',
                        'Admin chiude ticket → Stato "Chiuso" + Request feedback'
                    ]
                ]
            ],

            'settings' => [
                'title' => '⚙️ Impostazioni',
                'icon' => 'cog',
                'priority' => 10,
                'description' => 'Configurazioni scuola, PayPal, ricevute e personalizzazioni.',
                'content' => [
                    'intro' => 'Personalizza tutte le impostazioni della tua scuola: branding, pagamenti, ricevute e configurazioni operative.',
                    'operations' => [
                        'general' => [
                            'title' => '🏫 Impostazioni Generali Scuola',
                            'steps' => [
                                'Vai su "Gestione" → "Impostazioni" → Tab "Generale"',
                                'Modifica nome scuola ufficiale',
                                'Aggiungi logo scuola (formato PNG/JPG, max 2MB)',
                                'Inserisci indirizzo sede completo',
                                'Aggiungi contatti (telefono, email, PEC)',
                                'Imposta orari apertura segreteria',
                                'Inserisci link social media (Facebook, Instagram)',
                                'Aggiungi link sito web scuola se presente',
                                'Salva modifiche'
                            ],
                            'features' => [
                                'Nome e ragione sociale scuola',
                                'Upload logo per branding (dashboard, ricevute, email)',
                                'Indirizzo completo sede principale',
                                'Contatti multipli (telefono, email generale, PEC)',
                                'Orari apertura uffici/segreteria',
                                'Link social media (Facebook, Instagram, YouTube)',
                                'Link sito web istituzionale',
                                'Codice fiscale e Partita IVA'
                            ],
                            'tips' => 'Logo apparirà su ricevute e email automatiche: usa immagine professionale alta risoluzione.'
                        ],
                        'paypal' => [
                            'title' => '💳 Configurazione PayPal',
                            'steps' => [
                                'Vai su PayPal Developer (developer.paypal.com)',
                                'Crea app PayPal e ottieni Client ID + Secret',
                                'Torna su "Gestione" → "Impostazioni" → Tab "PayPal"',
                                'Incolla Client ID nel campo dedicato',
                                'Incolla Client Secret nel campo dedicato',
                                'Seleziona modalità: Sandbox (test) o Live (produzione)',
                                'Imposta valuta predefinita (EUR)',
                                'Salva configurazione',
                                'Clicca "Testa Connessione" per verificare',
                                'Se test ok → passa a modalità Live quando pronto'
                            ],
                            'features' => [
                                'Client ID e Secret sicuri (criptati database)',
                                'Modalità Sandbox per testing gratuito',
                                'Modalità Live per transazioni reali',
                                'Selezione valuta (EUR, USD, GBP...)',
                                'Test automatico connessione API',
                                'Log transazioni per debugging',
                                'Webhook automatici per conferme pagamento'
                            ],
                            'tips' => 'IMPORTANTISSIMO: Testa SEMPRE in Sandbox prima di attivare Live. Sandbox usa account PayPal finti (sandbox.paypal.com).'
                        ],
                        'receipts' => [
                            'title' => '🧾 Configurazione Ricevute',
                            'steps' => [
                                'Vai su "Gestione" → "Impostazioni" → Tab "Ricevute"',
                                'Personalizza intestazione ricevuta (nome scuola, logo)',
                                'Inserisci dati fiscali obbligatori (P.IVA, CF, reg. imprese)',
                                'Aggiungi indirizzo sede legale',
                                'Personalizza footer con note (es. "Grazie per la fiducia")',
                                'Imposta numerazione (prefisso + anno)',
                                'Salva template ricevuta',
                                'Clicca "Anteprima" per vedere risultato',
                                'Test: genera ricevuta campione'
                            ],
                            'features' => [
                                'Header personalizzabile con logo',
                                'Dati fiscali completi (P.IVA, CF, indirizzo legale)',
                                'Numerazione progressiva automatica anno per anno',
                                'Footer personalizzabile con note ringraziamento',
                                'Template PDF professionale',
                                'Anteprima live prima salvataggio',
                                'Firma digitale opzionale'
                            ],
                            'tips' => 'Dati fiscali DEVONO essere corretti per validità legale ricevute. Verifica con commercialista.'
                        ],
                        'notifications' => [
                            'title' => '🔔 Notifiche Email',
                            'features' => [
                                'Abilita/disabilita notifiche per evento (pagamento, iscrizione, ticket)',
                                'Personalizza oggetto e corpo email template',
                                'Imposta mittente email (nome scuola)',
                                'Configura reply-to per risposte',
                                'Test invio email'
                            ],
                            'tips' => 'Testa email prima attivazione per verificare non finiscano in spam.'
                        ]
                    ],
                    'best_practices' => [
                        'Completa setup Impostazioni prima di iniziare operatività',
                        'Testa PayPal in Sandbox almeno 5 transazioni prima Live',
                        'Verifica dati fiscali ricevute con commercialista',
                        'Fai backup configurazioni dopo ogni modifica importante',
                        'Aggiorna contatti scuola se cambiano telefono/email',
                        'Rivedi template email periodicamente per miglioramenti'
                    ],
                    'security_tips' => [
                        'NON condividere mai Client Secret PayPal',
                        'Cambia credenziali PayPal ogni 6 mesi',
                        'Verifica regolarmente log accessi impostazioni',
                        'Limita accesso Settings solo ad admin fidati',
                        'Fai screenshot configurazione come backup'
                    ]
                ]
            ],

            'troubleshooting' => [
                'title' => '🔧 Risoluzione Problemi',
                'icon' => 'wrench',
                'priority' => 11,
                'description' => 'Soluzioni ai problemi comuni e supporto tecnico.',
                'content' => [
                    'intro' => 'Guida rapida per risolvere i problemi più comuni della piattaforma. Se non trovi soluzione qui, contatta il supporto tecnico.',
                    'common_issues' => [
                        'payments' => [
                            'title' => '💳 Problemi Pagamenti',
                            'issues' => [
                                'PayPal non funziona → Verifica Client ID e Secret in Impostazioni. Controlla modalità (Sandbox vs Live)',
                                'Pagamento non registrato → Vai in "Pagamenti" → controlla filtri. Verifica log transazioni PayPal',
                                'Rimborso fallito → Verifica saldo PayPal sufficiente. Se problema persiste contatta PayPal support',
                                'Ricevuta non generata → Controlla configurazione ricevute in Impostazioni. Verifica dati fiscali completi',
                                'Email ricevuta non arrivata → Controlla spam. Verifica email studente corretta in profilo',
                                'Errore "Payment already processed" → Pagamento già completato. Verifica storico transazioni',
                                'Webhook PayPal non funzionano → Controlla URL webhook configurato in PayPal Developer app'
                            ]
                        ],
                        'students' => [
                            'title' => '👥 Problemi Studenti',
                            'issues' => [
                                'Studente non vede corso → Verifica corso "Attivo" e pubblicato. Controlla iscrizione studente al corso',
                                'Iscrizione bloccata → Controlla posti disponibili corso. Verifica documenti studente approvati',
                                'Login non funziona → Vai in profilo studente → "Reset Password" → invia email reset',
                                'Email non ricevute → Verifica email corretta in profilo. Chiedi studente controllare spam',
                                'Documenti non caricabili → Verifica dimensione file <5MB. Formati supportati: PDF, JPG, PNG',
                                'Presenze sbagliate → Vai in Presenze → seleziona lezione → correggi manualmente',
                                'Studente duplicato → Non eliminare! Contatta supporto per merge profili'
                            ]
                        ],
                        'courses' => [
                            'title' => '📚 Problemi Corsi',
                            'issues' => [
                                'Orario non salvato → Verifica sala disponibile. Controlla conflitti istruttore',
                                'Corso non visibile studenti → Verifica toggle "Corso Attivo" ON. Controlla data inizio/fine',
                                'Sale in conflitto → Vai in Gestisci Sale → verifica calendario occupazione',
                                'Lista studenti vuota → Controlla filtri applicati. Verifica iscrizioni corso',
                                'Duplicazione corso fallita → Riprova. Se persiste contatta supporto'
                            ]
                        ],
                        'data' => [
                            'title' => '📊 Problemi Dati e Report',
                            'issues' => [
                                'Dati non aggiornati → Ricarica pagina (CTRL+F5 o CMD+R). Svuota cache browser',
                                'Report vuoti → Verifica filtri date corretti. Controlla periodo selezionato ha dati',
                                'Export non scarica → Disabilita ad-blocker. Prova browser diverso (Chrome/Firefox)',
                                'Grafici non caricano → Ricarica pagina. Verifica JavaScript abilitato',
                                'Statistiche errate → Attendi sync automatico (max 1h). Se persiste segnala supporto',
                                'Performance lenta → Troppi dati visualizzati. Usa filtri per ridurre set dati'
                            ]
                        ],
                        'technical' => [
                            'title' => '🔧 Problemi Tecnici',
                            'issues' => [
                                'Pagina bianca → Svuota cache browser (CTRL+SHIFT+CANC). Riprova',
                                'Errore 500 → Problema temporaneo server. Attendi 5 min e riprova',
                                'Errore 403 → Non hai permessi. Verifica ruolo utente. Contatta admin',
                                'Sessione scaduta → Login di nuovo. Imposta "Ricordami" per sessioni lunghe',
                                'Upload fallito → Verifica dimensione file. Controlla connessione internet',
                                'Modifiche non salvate → Non chiudere finestra durante salvataggio. Attendi conferma'
                            ]
                        ]
                    ],
                    'workflow' => [
                        '1. Identifica problema specifico',
                        '2. Cerca soluzione in questa guida',
                        '3. Prova soluzioni suggerite',
                        '4. Se non risolto: raccogli info (screenshot, messaggio errore)',
                        '5. Apri ticket supporto con dettagli completi'
                    ],
                    'best_practices' => [
                        'Fai screenshot errori PRIMA di chiudere finestra',
                        'Annota esattamente i passi che causano problema',
                        'Verifica sempre cache browser per problemi visualizzazione',
                        'Testa su browser diverso se problema persiste',
                        'Non fare modifiche massive senza backup',
                        'Leggi TUTTI i messaggi errore (spesso contengono soluzione)'
                    ],
                    'security_tips' => [
                        'Se sospetti accesso non autorizzato → Cambia password IMMEDIATAMENTE',
                        'Transazioni sospette → Blocca PayPal e contatta supporto urgente',
                        'Dati mancanti → Verifica log accessi. Segnala a supporto se cancellazioni non autorizzate',
                        'Email phishing → NON cliccare link. Verifica mittente. Segnala a supporto'
                    ]
                ]
            ]
        ];
    }

    /**
     * Raccoglie statistiche scuola per contestualizzare la guida
     *
     * @param  \App\Models\School  $school
     * @return array
     */
    private function getSchoolStats($school)
    {
        try {
            return [
                'total_students' => $school->users()->where('role', 'student')->count(),
                'active_courses' => $school->courses()->where('active', true)->count(),
                'pending_payments' => \App\Models\Payment::whereHas('user', function($q) use ($school) {
                    $q->where('school_id', $school->id);
                })->where('status', 'pending')->count(),
                'open_tickets' => \App\Models\Ticket::whereHas('user', function($q) use ($school) {
                    $q->where('school_id', $school->id);
                })->whereIn('status', ['open', 'pending'])->count(),
                'total_staff' => $school->users()->where('role', 'admin')->count(),
                'school_name' => $school->name,
                'version' => 'v1.0.0'
            ];
        } catch (\Exception $e) {
            return [
                'total_students' => 0,
                'active_courses' => 0,
                'pending_payments' => 0,
                'open_tickets' => 0,
                'total_staff' => 1,
                'school_name' => $school->name ?? 'Scuola',
                'version' => 'v1.0.0'
            ];
        }
    }
}
