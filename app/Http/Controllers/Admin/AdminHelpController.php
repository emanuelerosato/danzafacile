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
                    'intro' => 'La Dashboard Admin è il centro di controllo della tua scuola di danza. Da qui puoi gestire corsi, studenti, pagamenti e tutte le attività operative.',
                    'key_features' => [
                        'Gestione completa corsi e calendario',
                        'Iscrizioni e gestione studenti',
                        'Sistema pagamenti integrato',
                        'Reports e analytics scuola',
                        'Comunicazioni con studenti e staff'
                    ],
                    'getting_started' => 'Inizia esplorando le statistiche nella dashboard home, poi naviga attraverso le sezioni principali usando la sidebar sinistra.',
                    'permissions' => 'Come Admin hai accesso completo ai dati della tua scuola ma non puoi accedere ad altre scuole.'
                ]
            ],

            'courses' => [
                'title' => '📚 Gestione Corsi',
                'icon' => 'academic-cap',
                'priority' => 2,
                'description' => 'Come creare e gestire corsi, orari, sale e programmi didattici.',
                'content' => [
                    'intro' => 'La sezione Corsi ti permette di gestire tutta l\'offerta formativa della scuola.',
                    'operations' => [
                        'create' => [
                            'title' => 'Creare un Nuovo Corso',
                            'steps' => [
                                'Vai su "Gestione Corsi" → "Corsi" → "Aggiungi Corso"',
                                'Compila nome, descrizione e livello del corso',
                                'Assegna istruttore e sala',
                                'Configura orari (giorni e fasce orarie)',
                                'Imposta prezzo e posti disponibili',
                                'Attiva il corso per renderlo visibile agli studenti'
                            ],
                            'tips' => 'Verifica sempre disponibilità sale e istruttori prima di creare un corso.'
                        ],
                        'manage' => [
                            'title' => 'Gestire Corsi Esistenti',
                            'features' => [
                                'Visualizzazione calendario corsi',
                                'Modifica orari e istruttori',
                                'Gestione posti disponibili',
                                'Attivazione/Disattivazione corsi',
                                'Duplicazione corsi per nuove stagioni'
                            ]
                        ],
                        'rooms' => [
                            'title' => 'Gestione Sale',
                            'description' => 'Crea e gestisci le sale della scuola per assegnarle ai corsi',
                            'features' => [
                                'Capienza sale',
                                'Disponibilità oraria',
                                'Prenotazioni e conflitti'
                            ]
                        ]
                    ]
                ]
            ],

            'students' => [
                'title' => '👥 Gestione Studenti',
                'icon' => 'users',
                'priority' => 3,
                'description' => 'Iscrizioni, presenze, documenti e comunicazioni con gli studenti.',
                'content' => [
                    'intro' => 'Gestisci tutti gli aspetti relativi agli studenti della scuola.',
                    'operations' => [
                        'enrollments' => [
                            'title' => 'Iscrizioni',
                            'description' => 'Gestisci le iscrizioni degli studenti ai corsi',
                            'actions' => [
                                'Iscrizione manuale studenti',
                                'Approvazione richieste online',
                                'Gestione liste d\'attesa',
                                'Trasferimenti tra corsi',
                                'Cancellazioni e rimborsi'
                            ]
                        ],
                        'attendance' => [
                            'title' => 'Presenze',
                            'description' => 'Traccia le presenze degli studenti alle lezioni',
                            'features' => [
                                'Registrazione presenze/assenze',
                                'Report presenze per studente',
                                'Statistiche frequenza corsi',
                                'Alert per assenze ripetute'
                            ]
                        ],
                        'documents' => [
                            'title' => 'Documenti',
                            'description' => 'Gestisci certificati medici e documenti studenti',
                            'types' => [
                                'Certificati medici',
                                'Autorizzazioni genitori',
                                'Documenti identità',
                                'Attestati e diplomi'
                            ]
                        ]
                    ]
                ]
            ],

            'payments' => [
                'title' => '💳 Pagamenti',
                'icon' => 'credit-card',
                'priority' => 4,
                'description' => 'Gestione pagamenti, ricevute, rimborsi e fatturazione.',
                'content' => [
                    'intro' => 'Sistema completo per gestire tutti i pagamenti della scuola.',
                    'features' => [
                        'payment_methods' => [
                            'title' => 'Metodi di Pagamento',
                            'available' => [
                                'PayPal (online)',
                                'Contanti (in sede)',
                                'Bonifico bancario',
                                'Carte di credito (Stripe)'
                            ]
                        ],
                        'invoicing' => [
                            'title' => 'Fatturazione',
                            'capabilities' => [
                                'Generazione ricevute automatiche',
                                'Fatture elettroniche',
                                'Export dati per commercialista',
                                'Report fiscali'
                            ]
                        ],
                        'refunds' => [
                            'title' => 'Rimborsi',
                            'process' => [
                                'Richiesta rimborso studente',
                                'Verifica condizioni rimborso',
                                'Emissione nota di credito',
                                'Rimborso su metodo originale'
                            ]
                        ]
                    ],
                    'settings' => [
                        'description' => 'Configura PayPal, ricevute e dati fiscali in "Gestione" → "Impostazioni"'
                    ]
                ]
            ],

            'events' => [
                'title' => '🎭 Eventi',
                'icon' => 'calendar',
                'priority' => 5,
                'description' => 'Organizza saggi, competizioni e eventi speciali.',
                'content' => [
                    'intro' => 'Crea e gestisci eventi speciali oltre ai corsi regolari.',
                    'event_types' => [
                        'Saggi di fine anno',
                        'Stage con maestri ospiti',
                        'Competizioni',
                        'Open day e lezioni prova',
                        'Feste a tema'
                    ],
                    'management' => [
                        'create' => [
                            'title' => 'Creare un Evento',
                            'steps' => [
                                'Definisci tipo, data e location',
                                'Imposta prezzo e posti disponibili',
                                'Crea descrizione e locandina',
                                'Pubblica per le registrazioni',
                                'Gestisci registrazioni e pagamenti'
                            ]
                        ],
                        'registrations' => [
                            'title' => 'Gestione Registrazioni',
                            'features' => [
                                'Elenco partecipanti',
                                'Conferme presenza',
                                'Comunicazioni di gruppo',
                                'Lista presenze evento'
                            ]
                        ]
                    ]
                ]
            ],

            'staff' => [
                'title' => '👨‍🏫 Staff & Istruttori',
                'icon' => 'user-group',
                'priority' => 6,
                'description' => 'Gestione team, orari, turni e paghe collaboratori.',
                'content' => [
                    'intro' => 'Organizza e coordina il team di istruttori e staff.',
                    'management' => [
                        'profiles' => [
                            'title' => 'Profili Staff',
                            'includes' => [
                                'Dati anagrafici e contatti',
                                'Specializzazioni e qualifiche',
                                'Disponibilità oraria',
                                'Storico corsi insegnati'
                            ]
                        ],
                        'schedules' => [
                            'title' => 'Orari e Turni',
                            'features' => [
                                'Calendario turni personale',
                                'Gestione sostituzioni',
                                'Alert conflitti orari',
                                'Export orari PDF'
                            ]
                        ],
                        'payroll' => [
                            'title' => 'Gestione Paghe',
                            'description' => 'Traccia ore lavorate e genera report per le paghe',
                            'reports' => [
                                'Ore lavorate per periodo',
                                'Calcolo compensi',
                                'Export per paghe'
                            ]
                        ]
                    ]
                ]
            ],

            'reports' => [
                'title' => '📊 Reports & Analytics',
                'icon' => 'chart-bar',
                'priority' => 7,
                'description' => 'Analytics, KPI e report operativi della scuola.',
                'content' => [
                    'intro' => 'Monitora le performance della scuola con report dettagliati.',
                    'available_reports' => [
                        'financial' => [
                            'title' => 'Report Finanziari',
                            'includes' => [
                                'Fatturato mensile/annuale',
                                'Incassi per corso',
                                'Pagamenti in sospeso',
                                'Trend crescita ricavi'
                            ]
                        ],
                        'students' => [
                            'title' => 'Report Studenti',
                            'includes' => [
                                'Nuove iscrizioni periodo',
                                'Retention rate',
                                'Presenze medie per corso',
                                'Dropout analysis'
                            ]
                        ],
                        'courses' => [
                            'title' => 'Report Corsi',
                            'includes' => [
                                'Corsi più popolari',
                                'Tasso riempimento sale',
                                'Performance istruttori',
                                'Soddisfazione studenti'
                            ]
                        ]
                    ],
                    'export_formats' => [
                        'PDF per presentazioni',
                        'Excel per analisi',
                        'CSV per elaborazioni esterne'
                    ]
                ]
            ],

            'tickets' => [
                'title' => '🎫 Sistema Ticket',
                'icon' => 'chat',
                'priority' => 8,
                'description' => 'Gestisci richieste supporto e comunicazioni studenti.',
                'content' => [
                    'intro' => 'Centralizza tutte le richieste di supporto degli studenti.',
                    'features' => [
                        'workflow' => [
                            'title' => 'Gestione Ticket',
                            'steps' => [
                                'Studente invia richiesta',
                                'Admin riceve notifica',
                                'Risposta e risoluzione problema',
                                'Chiusura ticket',
                                'Feedback studente'
                            ]
                        ],
                        'priorities' => [
                            'title' => 'Priorità',
                            'levels' => [
                                'Bassa - Informazioni generali',
                                'Media - Richieste amministrative',
                                'Alta - Problemi urgenti',
                                'Critica - Emergenze'
                            ]
                        ],
                        'categories' => [
                            'Problemi tecnici',
                            'Pagamenti e rimborsi',
                            'Corsi e lezioni',
                            'Account e profilo',
                            'Informazioni generali'
                        ]
                    ],
                    'bulk_actions' => [
                        'description' => 'Gestisci più ticket contemporaneamente',
                        'actions' => [
                            'Chiusura multipla',
                            'Assegnazione a staff',
                            'Cambio priorità'
                        ]
                    ]
                ]
            ],

            'settings' => [
                'title' => '⚙️ Impostazioni',
                'icon' => 'cog',
                'priority' => 9,
                'description' => 'Configurazioni scuola, PayPal, ricevute e personalizzazioni.',
                'content' => [
                    'intro' => 'Personalizza le impostazioni della tua scuola.',
                    'configuration_areas' => [
                        'general' => [
                            'title' => 'Impostazioni Generali',
                            'options' => [
                                'Nome e branding scuola',
                                'Contatti e sede',
                                'Orari apertura',
                                'Social media e sito web'
                            ]
                        ],
                        'paypal' => [
                            'title' => 'Configurazione PayPal',
                            'setup' => [
                                'Client ID e Secret da PayPal Developer',
                                'Modalità Sandbox (test) o Live',
                                'Valuta (EUR di default)',
                                'Test transazione prima attivazione'
                            ],
                            'tips' => 'Testa sempre in modalità Sandbox prima di attivare Live!'
                        ],
                        'receipts' => [
                            'title' => 'Ricevute',
                            'customization' => [
                                'Header personalizzato',
                                'Dati fiscali scuola',
                                'Footer con note',
                                'Logo scuola'
                            ]
                        ]
                    ]
                ]
            ],

            'troubleshooting' => [
                'title' => '🔧 Risoluzione Problemi',
                'icon' => 'wrench',
                'priority' => 10,
                'description' => 'Soluzioni ai problemi comuni e supporto tecnico.',
                'content' => [
                    'intro' => 'Guida rapida per risolvere i problemi più comuni.',
                    'common_issues' => [
                        'payments' => [
                            'title' => 'Problemi Pagamenti',
                            'issues' => [
                                'PayPal non funziona → Verifica credenziali in Impostazioni',
                                'Pagamento non registrato → Controlla log transazioni',
                                'Rimborso fallito → Contatta supporto PayPal',
                            ]
                        ],
                        'students' => [
                            'title' => 'Problemi Studenti',
                            'issues' => [
                                'Studente non vede corso → Verifica corso attivo',
                                'Iscrizione bloccata → Controlla posti disponibili',
                                'Login non funziona → Reset password studente'
                            ]
                        ],
                        'data' => [
                            'title' => 'Problemi Dati',
                            'issues' => [
                                'Dati non aggiornati → Ricarica pagina (F5)',
                                'Report vuoti → Verifica filtri date',
                                'Export non scarica → Disabilita ad-blocker'
                            ]
                        ]
                    ],
                    'support' => [
                        'title' => 'Come Ottenere Supporto',
                        'channels' => [
                            '🎫 Apri ticket supporto → Risposta entro 24h',
                            '📧 Email supporto@scuoladanza.com',
                            '📱 Numero verde assistenza',
                            '📚 Documentazione online completa'
                        ]
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
                'total_students' => $school->users()->where('role', 'user')->count(),
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
