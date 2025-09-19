<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa studenti dal vecchio database SQL';

    private const SCHOOL_ID = 12; // Daniel's Dance School
    private const DEFAULT_PASSWORD = 'TempPass2025!';
    private const SQL_FILE = '/var/www/html/vecchiodb.sql';

    private $imported = 0;
    private $skipped = 0;
    private $errors = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Inizio importazione studenti dal vecchio database...');
        $this->newLine();

        // Leggi il file SQL
        if (!file_exists(self::SQL_FILE)) {
            $this->error("❌ File non trovato: " . self::SQL_FILE);
            return 1;
        }

        $sqlContent = file_get_contents(self::SQL_FILE);
        if (!$sqlContent) {
            $this->error("❌ Impossibile leggere il file " . self::SQL_FILE);
            return 1;
        }

        // Estrai i dati degli studenti
        $students = $this->extractStudentsFromSql($sqlContent);
        $this->info("📋 Trovati " . count($students) . " studenti nel file SQL");
        $this->newLine();

        // Chiedi conferma
        if (!$this->confirm('Procedere con l\'importazione?')) {
            $this->warn('❌ Operazione annullata dall\'utente');
            return 0;
        }

        // Importa gli studenti
        $this->importStudents($students);

        // Report finale
        $this->printFinalReport();

        return 0;
    }

    private function extractStudentsFromSql($sqlContent)
    {
        $students = [];

        // Pattern per estrarre i dati dalla tabella socio
        $pattern = '/INSERT INTO `socio` \([^)]+\) VALUES\s*(.+?)(?=\n\n|INSERT INTO|CREATE TABLE|$)/s';

        if (preg_match($pattern, $sqlContent, $matches)) {
            $valuesSection = $matches[1];

            // Estrai ogni riga di dati
            $lines = explode("\n", $valuesSection);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || !preg_match('/^\(/', $line)) {
                    continue;
                }

                // Rimuovi virgola finale e parentesi
                $line = rtrim($line, ',');

                // Estrai i valori usando un parser più robusto
                $studentData = $this->parseStudentLine($line);
                if ($studentData) {
                    $students[] = $studentData;
                }
            }
        }

        return $students;
    }

    private function parseStudentLine($line)
    {
        // Pattern per estrarre i valori tra parentesi
        if (!preg_match('/^\((.+)\)$/', $line, $matches)) {
            return null;
        }

        $valuesString = $matches[1];

        // Split sui valori separati da virgola, gestendo le stringhe quotate
        $values = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = null;

        for ($i = 0; $i < strlen($valuesString); $i++) {
            $char = $valuesString[$i];

            if (!$inQuotes && ($char === "'" || $char === '"')) {
                $inQuotes = true;
                $quoteChar = $char;
                continue;
            } elseif ($inQuotes && $char === $quoteChar) {
                // Controlla se è un escape
                if ($i + 1 < strlen($valuesString) && $valuesString[$i + 1] === $quoteChar) {
                    $current .= $char;
                    $i++; // Skip next quote
                    continue;
                }
                $inQuotes = false;
                $quoteChar = null;
                continue;
            } elseif (!$inQuotes && $char === ',') {
                $values[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        // Aggiungi l'ultimo valore
        if ($current !== '') {
            $values[] = trim($current);
        }

        // Verifica che abbiamo il numero corretto di colonne
        if (count($values) < 10) {
            $this->warn("⚠️  Riga malformata, saltata: " . substr($line, 0, 100) . "...");
            return null;
        }

        // Mappa i valori alle colonne
        // cf, email, nome, cognome, sesso, password, corso, cellulare, attivo, ruolo, created_at, updated_at, last_login, email_verificata, note, avatar, reset_token, reset_token_expire
        return [
            'codice_fiscale' => $this->cleanValue($values[0]),
            'email' => $this->cleanValue($values[1]),
            'first_name' => $this->cleanValue($values[2]),
            'last_name' => $this->cleanValue($values[3]),
            'gender' => $this->cleanValue($values[4]),
            'old_password' => $this->cleanValue($values[5]),
            'course_id' => $this->cleanValue($values[6]),
            'phone' => $this->cleanValue($values[7]),
            'active' => $this->cleanValue($values[8]) === '1',
            'role' => $this->cleanValue($values[9]),
        ];
    }

    private function cleanValue($value)
    {
        if ($value === 'NULL' || $value === 'null') {
            return null;
        }

        return trim($value, " \t\n\r\0\x0B'\"");
    }

    private function importStudents($students)
    {
        $this->info("📥 Inizio importazione di " . count($students) . " studenti...");
        $this->newLine();

        $progressBar = $this->output->createProgressBar(count($students));
        $progressBar->start();

        DB::beginTransaction();

        try {
            foreach ($students as $index => $studentData) {
                $this->importSingleStudent($studentData, $index + 1);
                $progressBar->advance();
            }

            DB::commit();
            $progressBar->finish();
            $this->newLine();
            $this->info("✅ Transazione completata con successo!");

        } catch (\Exception $e) {
            DB::rollback();
            $progressBar->finish();
            $this->newLine();
            $this->error("❌ Errore durante l'importazione, rollback eseguito: " . $e->getMessage());
            throw $e;
        }
    }

    private function importSingleStudent($data, $index)
    {
        try {
            // Skip se non è uno studente
            if ($data['role'] !== 'user') {
                $this->skipped++;
                return;
            }

            // Verifica duplicati email
            if (User::where('email', $data['email'])->exists()) {
                $this->skipped++;
                return;
            }

            // Verifica duplicati codice fiscale
            if (!empty($data['codice_fiscale']) && User::where('codice_fiscale', $data['codice_fiscale'])->exists()) {
                $this->skipped++;
                return;
            }

            // Crea l'utente
            User::create([
                'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'school_id' => self::SCHOOL_ID,
                'role' => 'user', // Studente
                'phone' => $this->formatPhone($data['phone']),
                'codice_fiscale' => $data['codice_fiscale'],
                'active' => $data['active'] ?? true,
            ]);

            $this->imported++;

        } catch (\Exception $e) {
            $this->errors[] = "Errore #{$index} ({$data['email']}): " . $e->getMessage();
        }
    }

    private function formatPhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Rimuovi caratteri non numerici tranne il +
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // Se inizia con 39 ma non con +39, aggiungi il +
        if (preg_match('/^39\d{10}$/', $phone)) {
            $phone = '+' . $phone;
        }

        // Se è un numero italiano senza prefisso, aggiungilo
        if (preg_match('/^3\d{9}$/', $phone)) {
            $phone = '+39' . $phone;
        }

        return $phone;
    }

    private function printFinalReport()
    {
        $this->newLine();
        $this->line(str_repeat("=", 60));
        $this->info("📊 REPORT FINALE IMPORTAZIONE STUDENTI");
        $this->line(str_repeat("=", 60));
        $this->info("✅ Studenti importati: {$this->imported}");
        $this->info("⏭️  Studenti saltati: {$this->skipped}");
        $this->info("❌ Errori: " . count($this->errors));
        $this->info("🏫 Scuola assegnata: Daniel's Dance School (ID: " . self::SCHOOL_ID . ")");
        $this->info("🔑 Password temporanea: " . self::DEFAULT_PASSWORD);

        if (!empty($this->errors)) {
            $this->newLine();
            $this->warn("📋 Dettaglio errori:");
            foreach ($this->errors as $error) {
                $this->line("   • {$error}");
            }
        }

        $this->newLine();
        $this->info("🎉 Importazione completata!");
        $this->line(str_repeat("=", 60));
    }
}
