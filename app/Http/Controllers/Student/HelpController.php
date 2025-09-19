<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Display the help index page for students.
     */
    public function index()
    {
        $helpSections = [
            [
                'id' => 'getting-started',
                'title' => 'Come Iniziare',
                'icon' => 'play-circle',
                'description' => 'I primi passi nella piattaforma',
                'articles' => [
                    'Accesso alla piattaforma',
                    'Completare il profilo',
                    'Esplorare la dashboard',
                    'Prime impostazioni'
                ]
            ],
            [
                'id' => 'courses',
                'title' => 'Corsi e Iscrizioni',
                'icon' => 'academic-cap',
                'description' => 'Tutto sui corsi di danza',
                'articles' => [
                    'Come iscriversi a un corso',
                    'Visualizzare il calendario lezioni',
                    'Gestire le assenze',
                    'Cambiare corso'
                ]
            ],
            [
                'id' => 'payments',
                'title' => 'Pagamenti',
                'icon' => 'credit-card',
                'description' => 'Gestione pagamenti e fatture',
                'articles' => [
                    'Metodi di pagamento accettati',
                    'Scadenze e rate',
                    'Scaricare le ricevute',
                    'Problemi con i pagamenti'
                ]
            ],
            [
                'id' => 'documents',
                'title' => 'Documenti',
                'icon' => 'document-text',
                'description' => 'Caricare e gestire documenti',
                'articles' => [
                    'Documenti richiesti',
                    'Come caricare i file',
                    'Formati supportati',
                    'Stato approvazione'
                ]
            ],
            [
                'id' => 'communication',
                'title' => 'Comunicazione',
                'icon' => 'chat-bubble-left-right',
                'description' => 'Messaggi e notifiche',
                'articles' => [
                    'Inviare messaggi alla scuola',
                    'Ricevere notifiche',
                    'Contattare gli insegnanti',
                    'Comunicazioni di emergenza'
                ]
            ],
            [
                'id' => 'account',
                'title' => 'Il Mio Account',
                'icon' => 'user-circle',
                'description' => 'Gestione profilo e privacy',
                'articles' => [
                    'Modificare i dati personali',
                    'Cambiare password',
                    'Impostazioni privacy',
                    'Eliminare l\'account'
                ]
            ]
        ];

        $frequentQuestions = [
            [
                'question' => 'Come posso iscrivermi a un nuovo corso?',
                'answer' => 'Vai nella sezione "Corsi" dalla dashboard, cerca il corso che ti interessa e clicca su "Iscriviti". Segui le istruzioni per completare l\'iscrizione e il pagamento.'
            ],
            [
                'question' => 'Cosa devo fare se non posso partecipare a una lezione?',
                'answer' => 'Ti consigliamo di comunicarlo in anticipo tramite la sezione messaggi. Le assenze giustificate possono essere recuperate secondo il regolamento della scuola.'
            ],
            [
                'question' => 'Come posso scaricare la ricevuta di pagamento?',
                'answer' => 'Vai nella sezione "Pagamenti" della tua dashboard, trova la transazione e clicca su "Scarica ricevuta". Il documento sarà disponibile in formato PDF.'
            ],
            [
                'question' => 'Quali documenti devo caricare?',
                'answer' => 'I documenti richiesti includono: documento d\'identità, certificato medico per attività sportiva e eventuale autorizzazione per minorenni. Controlla la sezione "Documenti" per l\'elenco completo.'
            ],
            [
                'question' => 'Come posso contattare la scuola?',
                'answer' => 'Puoi inviare messaggi direttamente dalla piattaforma tramite la sezione "Messaggi", chiamare il numero della scuola o scrivere un\'email. Tutti i contatti sono disponibili nella sezione contatti.'
            ]
        ];

        return view('student.help.index', compact('helpSections', 'frequentQuestions'));
    }

    /**
     * Display a specific help section.
     */
    public function section($section)
    {
        $content = $this->getHelpContent($section);

        if (!$content) {
            abort(404);
        }

        return view('student.help.section', compact('content', 'section'));
    }

    /**
     * Get help content for a specific section.
     */
    private function getHelpContent($section)
    {
        $content = [
            'getting-started' => [
                'title' => 'Come Iniziare',
                'icon' => 'play-circle',
                'articles' => [
                    [
                        'title' => 'Accesso alla piattaforma',
                        'content' => 'Per accedere alla piattaforma, utilizza le credenziali che hai ricevuto via email al momento dell\'iscrizione. Se hai dimenticato la password, clicca su "Password dimenticata?" nella pagina di login.'
                    ],
                    [
                        'title' => 'Completare il profilo',
                        'content' => 'È importante completare il tuo profilo con tutte le informazioni richieste. Vai su "Il Mio Profilo" e compila tutti i campi obbligatori: dati anagrafici, contatti e preferenze.'
                    ],
                    [
                        'title' => 'Esplorare la dashboard',
                        'content' => 'La dashboard è il punto centrale della piattaforma. Qui puoi vedere i tuoi corsi attivi, le prossime lezioni, i messaggi importanti e accedere rapidamente a tutte le funzioni.'
                    ]
                ]
            ],
            'courses' => [
                'title' => 'Corsi e Iscrizioni',
                'icon' => 'academic-cap',
                'articles' => [
                    [
                        'title' => 'Come iscriversi a un corso',
                        'content' => 'Per iscriverti a un corso: 1) Vai nella sezione "Corsi", 2) Sfoglia i corsi disponibili o usa i filtri, 3) Clicca su "Dettagli" per vedere le informazioni complete, 4) Clicca su "Iscriviti" e segui le istruzioni per il pagamento.'
                    ],
                    [
                        'title' => 'Visualizzare il calendario lezioni',
                        'content' => 'Il calendario delle tue lezioni è visibile nella dashboard principale e nella sezione "I Miei Corsi". Puoi vedere orari, sale e eventuali variazioni programmate.'
                    ],
                    [
                        'title' => 'Gestire le assenze',
                        'content' => 'In caso di assenza, è importante comunicarlo tramite la piattaforma o contattando direttamente la scuola. Le assenze giustificate per motivi medici possono essere recuperate secondo il regolamento.'
                    ]
                ]
            ],
            'payments' => [
                'title' => 'Pagamenti',
                'icon' => 'credit-card',
                'articles' => [
                    [
                        'title' => 'Metodi di pagamento accettati',
                        'content' => 'Accettiamo pagamenti tramite: carte di credito/debito (Visa, Mastercard), bonifico bancario, PayPal e pagamenti in contanti presso la segreteria della scuola.'
                    ],
                    [
                        'title' => 'Scadenze e rate',
                        'content' => 'I pagamenti possono essere effettuati in un\'unica soluzione o a rate mensili. Le scadenze sono chiaramente indicate nella sezione "Pagamenti" e riceverai promemoria via email.'
                    ],
                    [
                        'title' => 'Scaricare le ricevute',
                        'content' => 'Tutte le ricevute sono disponibili nella sezione "Pagamenti". Clicca su "Scarica" accanto a ogni transazione per ottenere il documento PDF ufficiale.'
                    ]
                ]
            ],
            'documents' => [
                'title' => 'Documenti',
                'icon' => 'document-text',
                'articles' => [
                    [
                        'title' => 'Documenti richiesti',
                        'content' => 'I documenti necessari includono: documento d\'identità valido, certificato medico per attività sportiva non agonistica (validità 1 anno), autorizzazione per minorenni firmata dai genitori.'
                    ],
                    [
                        'title' => 'Come caricare i file',
                        'content' => 'Vai nella sezione "Documenti", clicca su "Carica Documento", seleziona il tipo di documento, aggiungi una descrizione e carica il file. I formati supportati sono PDF, JPG, PNG.'
                    ],
                    [
                        'title' => 'Stato approvazione',
                        'content' => 'Dopo il caricamento, i documenti vengono verificati dal personale della scuola. Gli stati possibili sono: In Attesa (giallo), Approvato (verde), Rifiutato (rosso). In caso di rifiuto, riceverai indicazioni per la correzione.'
                    ]
                ]
            ],
            'communication' => [
                'title' => 'Comunicazione',
                'icon' => 'chat-bubble-left-right',
                'articles' => [
                    [
                        'title' => 'Inviare messaggi alla scuola',
                        'content' => 'Usa la sezione "Messaggi" per comunicare con la scuola. Seleziona la categoria appropriata (generale, amministrativo, didattico) e scrivi il tuo messaggio. Riceverai una risposta entro 24-48 ore.'
                    ],
                    [
                        'title' => 'Ricevere notifiche',
                        'content' => 'Le notifiche importanti vengono inviate via email e sono visibili nella dashboard. Puoi personalizzare le preferenze di notifica nel tuo profilo.'
                    ],
                    [
                        'title' => 'Contattare gli insegnanti',
                        'content' => 'Per questioni didattiche specifiche, puoi contattare direttamente gli insegnanti tramite la piattaforma o durante gli orari di ricevimento pubblicati.'
                    ]
                ]
            ],
            'account' => [
                'title' => 'Il Mio Account',
                'icon' => 'user-circle',
                'articles' => [
                    [
                        'title' => 'Modificare i dati personali',
                        'content' => 'Puoi aggiornare i tuoi dati personali nella sezione "Il Mio Profilo". Alcune modifiche (come il nome) potrebbero richiedere verifica e approvazione da parte della segreteria.'
                    ],
                    [
                        'title' => 'Cambiare password',
                        'content' => 'Per motivi di sicurezza, cambia regolarmente la tua password. Vai nelle impostazioni account e scegli una password sicura con almeno 8 caratteri, lettere maiuscole, minuscole e numeri.'
                    ],
                    [
                        'title' => 'Impostazioni privacy',
                        'content' => 'Puoi gestire le tue preferenze privacy nelle impostazioni account. Decidi chi può vedere le tue informazioni e come desideri ricevere le comunicazioni.'
                    ]
                ]
            ]
        ];

        return $content[$section] ?? null;
    }
}