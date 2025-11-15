<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportOldStudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Inizio import studenti dal vecchio sistema...');

        // 1. Trova la scuola esistente
        $school = School::where('email', 'dds16042007@gmail.com')->first();

        if (!$school) {
            $this->command->error('❌ Scuola non trovata! Assicurati che esista una scuola con email: dds16042007@gmail.com');
            return;
        }

        $this->command->info("✅ Scuola trovata: {$school->name} (ID: {$school->id})");

        // 2. Elimina vecchio admin se esiste
        $oldAdmin = User::where('email', 'emanuelerosato.com@gmail.com')->first();
        if ($oldAdmin) {
            $oldAdmin->delete();
            $this->command->info('🗑️  Eliminato vecchio admin: emanuelerosato.com@gmail.com');
        }

        // 3. Crea nuovo admin se non esiste
        $newAdmin = User::where('email', 'dds16042007@gmail.com')->first();
        if (!$newAdmin) {
            User::create([
                'school_id' => $school->id,
                'name' => $school->name,
                'first_name' => 'Admin',
                'last_name' => $school->name,
                'email' => 'dds16042007@gmail.com',
                'password' => Hash::make(Str::random(16)), // Password random
                'role' => User::ROLE_ADMIN,
                'active' => true,
                'email_verified_at' => now(),
            ]);
            $this->command->info('✅ Creato nuovo admin: dds16042007@gmail.com');
        } else {
            $this->command->info('ℹ️  Admin già esistente: dds16042007@gmail.com');
        }

        // 4. CF da escludere (fake/test)
        $excludedCFs = [
            'ZZZZZZZZZZZZZZZZ',
            'sadsasdasdasadsa',
            '2132132123113123',
        ];

        // 5. Dati studenti dal vecchio sistema
        $oldStudents = $this->getOldStudentsData();

        $imported = 0;
        $excluded = 0;
        $errors = [];

        foreach ($oldStudents as $student) {
            // Escludi CF fake
            if (in_array($student['cf'], $excludedCFs)) {
                $excluded++;
                $this->command->warn("⏭️  Escluso (fake CF): {$student['nome']} {$student['cognome']} ({$student['email']})");
                continue;
            }

            // Verifica se email già esiste
            if (User::where('email', $student['email'])->exists()) {
                $this->command->warn("⏭️  Email già esistente: {$student['email']}");
                continue;
            }

            try {
                // Genera password random
                $randomPassword = Str::random(16);

                User::create([
                    'school_id' => $school->id,
                    'name' => trim($student['nome'] . ' ' . $student['cognome']),
                    'first_name' => trim($student['nome']),
                    'last_name' => trim($student['cognome']),
                    'email' => $student['email'],
                    'codice_fiscale' => $student['cf'],
                    'password' => Hash::make($randomPassword),
                    'phone' => $this->cleanPhone($student['cellulare']),
                    'role' => User::ROLE_STUDENT,
                    'active' => (bool) $student['attivo'],
                    'email_verified_at' => null, // Devono verificare email
                    'created_at' => $student['created_at'],
                ]);

                $imported++;

                if ($imported % 10 == 0) {
                    $this->command->info("📊 Importati: {$imported}");
                }

            } catch (\Exception $e) {
                $errors[] = "{$student['email']}: " . $e->getMessage();
            }
        }

        // Report finale
        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📊 REPORT IMPORT');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("✅ Studenti importati: {$imported}");
        $this->command->info("⏭️  Record esclusi (fake): {$excluded}");
        $this->command->info("❌ Errori: " . count($errors));

        if (!empty($errors)) {
            $this->command->error('Errori dettagliati:');
            foreach ($errors as $error) {
                $this->command->error("  - {$error}");
            }
        }

        $totalUsers = User::where('school_id', $school->id)->count();
        $this->command->info("👥 Totale utenti scuola: {$totalUsers}");
        $this->command->newLine();
    }

    /**
     * Pulisce numero telefono
     */
    private function cleanPhone(string $phone): string
    {
        // Rimuove spazi, +39 prefix, caratteri non numerici
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Se inizia con 39, rimuovilo
        if (str_starts_with($cleaned, '39')) {
            $cleaned = substr($cleaned, 2);
        }

        // Se troppo corto o lungo, ritorna originale
        if (strlen($cleaned) < 9 || strlen($cleaned) > 11) {
            return $phone;
        }

        return $cleaned;
    }

    /**
     * Dati studenti dal vecchio DB
     */
    private function getOldStudentsData(): array
    {
        return [
            ['cf' => 'RCSGLI14R68L049K', 'email' => 'adapiccoli@virgilio.it', 'nome' => 'Giulia', 'cognome' => 'Arces', 'sesso' => 'F', 'cellulare' => '3498161980', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'SLBRRT18A61D761E', 'email' => 'adrianaalo82@gmail.com', 'nome' => 'Roberta', 'cognome' => 'Siliberto', 'sesso' => 'F', 'cellulare' => '3496287930', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CVLCLT16R62D761C', 'email' => 'Albertovaio@yahoo.it', 'nome' => 'Carlotta', 'cognome' => 'Cavallo', 'sesso' => 'F', 'cellulare' => '+393394965', 'attivo' => 1, 'created_at' => '2025-09-14 15:14:02'],
            ['cf' => 'NCCRRA20S56E986H', 'email' => 'alessandronacci2021@gmail.com', 'nome' => 'Aurora', 'cognome' => 'Nacci', 'sesso' => 'F', 'cellulare' => '3892316058', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'FRNLSN99S27E205N', 'email' => 'alessandrovito.fornaro@gmail.com', 'nome' => 'Alessandro Vito', 'cognome' => 'Fornaro', 'sesso' => 'M', 'cellulare' => '3271383525', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'RDLMLY16S43D761G', 'email' => 'alessiadurante32@gmail.com', 'nome' => 'Emily', 'cognome' => 'Radulescu', 'sesso' => 'F', 'cellulare' => '3755660547', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'GSTLCA09C58L049J', 'email' => 'alicegiustizieri09@gmail.com', 'nome' => 'Alice', 'cognome' => 'Giustizieri', 'sesso' => 'F', 'cellulare' => '3402867992', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'ZZZZZZZZZZZZZZZZ', 'email' => 'andreafilomeno@yahoo.it', 'nome' => 'Andrea', 'cognome' => 'Filomeno', 'sesso' => 'M', 'cellulare' => '3333333333', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'GFFMHS10D16B180J', 'email' => 'andreeazima1@gmail.com', 'nome' => 'Mathias', 'cognome' => 'Goffredo', 'sesso' => 'M', 'cellulare' => '3804654090', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NDRNTN85E21E205Y', 'email' => 'andrianiantonio2105@gmail.com', 'nome' => 'Antonio', 'cognome' => 'Andriani', 'sesso' => 'M', 'cellulare' => '3247458957', 'attivo' => 1, 'created_at' => '2025-09-14 14:14:19'],
            ['cf' => 'RLNNGL79P54E205N', 'email' => 'angelaorlando7926@gmail.com', 'nome' => 'Angela', 'cognome' => 'Orlando', 'sesso' => 'F', 'cellulare' => '3491959973', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CRLNGL63C11C741I', 'email' => 'angelo.caroli1963@libero.it', 'nome' => 'Angelo', 'cognome' => 'Caroli', 'sesso' => 'M', 'cellulare' => '3287489422', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'BCCMTN11R48L049L', 'email' => 'annacaramia2022@libero.it', 'nome' => 'Martina', 'cognome' => 'Bucci', 'sesso' => 'F', 'cellulare' => '3208593593', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'RSMNNA03P58L049P', 'email' => 'annarismoo2003@gmail.com', 'nome' => 'Anna', 'cognome' => 'Rismondo', 'sesso' => 'M', 'cellulare' => '3284290315', 'attivo' => 1, 'created_at' => '2025-09-22 17:47:28'],
            ['cf' => 'MNGCLT17E50D761P', 'email' => 'annarita.romano1@icloud.com', 'nome' => 'Carlotta', 'cognome' => 'Manigrasso', 'sesso' => 'F', 'cellulare' => '3398171047', 'attivo' => 1, 'created_at' => '2025-09-14 15:32:39'],
            ['cf' => 'NNCMLS84R57E205R', 'email' => 'Annicchiaricomarialuisa@gmail.com', 'nome' => 'Elisa', 'cognome' => 'Dedja', 'sesso' => 'F', 'cellulare' => '3282732529', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MNDRRA16M62D761U', 'email' => 'antonellalisi88@gmail.com', 'nome' => 'Aurora', 'cognome' => 'Mondelli', 'sesso' => 'F', 'cellulare' => '3292930370', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LPURRA11A47B180P', 'email' => 'apruzzese_maria@libero.it', 'nome' => 'Aurora', 'cognome' => 'Lupo', 'sesso' => 'F', 'cellulare' => '3288356087', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'RGSNTN68B49E986G', 'email' => 'arianna.raguso68@gmail.com', 'nome' => 'Arianna', 'cognome' => 'Raguso', 'sesso' => 'F', 'cellulare' => '3296313699', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LCRSMN13C04C136W', 'email' => 'ariannacaiazzo@libero.it', 'nome' => 'Simone', 'cognome' => 'Lacorte', 'sesso' => 'M', 'cellulare' => '3490611640', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NNCNGL68B42C424B', 'email' => 'atelierannicchiarico@libero.it', 'nome' => 'Angela', 'cognome' => 'Annicchiarico', 'sesso' => 'F', 'cellulare' => '3495878318', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CRRPPZ76C54E205H', 'email' => 'aziacarrieri@gmail.com', 'nome' => 'Azia', 'cognome' => 'Carrieri', 'sesso' => 'F', 'cellulare' => '3470368528', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'BNCLSS16P61D761W', 'email' => 'biancoand1973@gmail.com', 'nome' => 'Alessia', 'cognome' => 'Bianco', 'sesso' => 'F', 'cellulare' => '3393138837', 'attivo' => 1, 'created_at' => '2025-09-17 07:23:35'],
            ['cf' => 'RSTGDI14R54E986F', 'email' => 'caliandrolucrezia1978@gmail.com', 'nome' => 'Giada', 'cognome' => 'Rosato', 'sesso' => 'F', 'cellulare' => '3270030094', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CMPFNC08B54E205O', 'email' => 'campanellaf74@gmail.com', 'nome' => 'Francesca', 'cognome' => 'Campanella', 'sesso' => 'F', 'cellulare' => '3917622664', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NSIMRP14P63L049T', 'email' => 'carmela.galeone@libero.it', 'nome' => 'Maria pia', 'cognome' => 'Nisi', 'sesso' => 'F', 'cellulare' => '3248293377', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LPPCML55R46C129T', 'email' => 'carmelalippolis55@gmail.com', 'nome' => 'Carmela', 'cognome' => 'Lippolis', 'sesso' => 'F', 'cellulare' => '3284109388', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MNDLRZ12B42L049A', 'email' => 'chefvip@hotmail.it', 'nome' => 'Lucrezia', 'cognome' => 'Amendola', 'sesso' => 'F', 'cellulare' => '3472633398', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MDCGAI19E62E986O', 'email' => 'cinziaaprile80@gmail.com', 'nome' => 'Gaia', 'cognome' => 'Medici', 'sesso' => 'F', 'cellulare' => '3401827844', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LNTCRI65B23E205W', 'email' => 'ciro.lenti@hotmail.com', 'nome' => 'Ciro', 'cognome' => 'Lenti', 'sesso' => 'M', 'cellulare' => '3281582168', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NSIGLI16M63H264J', 'email' => 'ciro85@msn.com', 'nome' => 'Giulia', 'cognome' => 'Nisi', 'sesso' => 'F', 'cellulare' => '3496146091', 'attivo' => 1, 'created_at' => '2025-09-15 15:39:08'],
            ['cf' => 'LPUCRI72T26E205V', 'email' => 'cirolupo1972@libero.it', 'nome' => 'Ciro', 'cognome' => 'Lupo', 'sesso' => 'M', 'cellulare' => '3246931823', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LPUFRC14P64E205Q', 'email' => 'cirolupo72@gmail.com', 'nome' => 'Federica', 'cognome' => 'Lupo', 'sesso' => 'F', 'cellulare' => '3246931823', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'BRTCLD06H61E205O', 'email' => 'claudiabritt18@gmail.com', 'nome' => 'Claudia', 'cognome' => 'Brittannico', 'sesso' => 'F', 'cellulare' => '3926096049', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'TTNCLO08P61L049Y', 'email' => 'cloeattanasi@gmail.com', 'nome' => 'CLOE', 'cognome' => 'ATTANASI', 'sesso' => 'F', 'cellulare' => '+393203243', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'TRNCMS64E50E986P', 'email' => 'comasiaturnone0@gmail.com', 'nome' => 'Comasia', 'cognome' => 'Turnone', 'sesso' => 'F', 'cellulare' => '3283619108', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CRSCSM74D62E205R', 'email' => 'crescenziomimma4791@gmail.com', 'nome' => 'Cosima', 'cognome' => 'Crescenzio', 'sesso' => 'F', 'cellulare' => '3249891643', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CRSDNL83D51E205U', 'email' => 'danielacre@alice.it', 'nome' => 'Daniela', 'cognome' => 'Crescenzio', 'sesso' => 'F', 'cellulare' => '3888517783', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'QRNRCH15H69E205N', 'email' => 'daniele4017@gmail.com', 'nome' => 'Aurora Chiara', 'cognome' => 'Quaranta', 'sesso' => 'F', 'cellulare' => '3479581796', 'attivo' => 1, 'created_at' => '2025-09-14 14:48:13'],
            ['cf' => 'CRVCRS13M55L049G', 'email' => 'danielecarovigna@hotmail.it', 'nome' => 'Christel', 'cognome' => 'Carovigna', 'sesso' => 'F', 'cellulare' => '3468781015', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'QRNGRT18L65E986Z', 'email' => 'el_quaranta@libero.it', 'nome' => 'Greta', 'cognome' => 'Quaranta', 'sesso' => 'F', 'cellulare' => '3479747969', 'attivo' => 1, 'created_at' => '2025-09-15 12:53:26'],
            ['cf' => 'GLNLCA17B64L049R', 'email' => 'ellevale@libero.it', 'nome' => 'Alice', 'cognome' => 'Galiano', 'sesso' => 'F', 'cellulare' => '3203058100', 'attivo' => 1, 'created_at' => '2025-09-26 15:56:10'],
            ['cf' => 'sadsasdasdasadsa', 'email' => 'emanuelerosato.com@gmail.com', 'nome' => 'asddsa', 'cognome' => 'sasadsasda', 'sesso' => 'M', 'cellulare' => 'sadsasasda', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CNGFNN12B24E986C', 'email' => 'emiliano.caniglia@libero.it', 'nome' => 'Fernando', 'cognome' => 'Caniglia', 'sesso' => 'M', 'cellulare' => '3493151311', 'attivo' => 1, 'created_at' => '2025-10-24 17:56:16'],
            ['cf' => 'CVLMNL11B14E205C', 'email' => 'family01rty@gmail.com', 'nome' => 'Emmanuele', 'cognome' => 'Cavallo', 'sesso' => 'M', 'cellulare' => '3290109867', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'PGLGAI14P55E205X', 'email' => 'franciterranova1@gmail.com', 'nome' => 'Gaia', 'cognome' => 'Pugliese', 'sesso' => 'F', 'cellulare' => '3398909663', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'SCPFNC57M04E205J', 'email' => 'fscappati@gmail.com', 'nome' => 'Franco', 'cognome' => 'Scappati', 'sesso' => 'M', 'cellulare' => '3292041441', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LGRMNL11E59E205P', 'email' => 'gennycaputo71@gmail.com', 'nome' => 'Manuela', 'cognome' => 'ligorio', 'sesso' => 'F', 'cellulare' => '3494964899', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NNCGAI13H60L049N', 'email' => 'giannicchiarico@gmail.com', 'nome' => 'Gaia', 'cognome' => 'Annicchiarico', 'sesso' => 'F', 'cellulare' => '3405320919', 'attivo' => 1, 'created_at' => '2025-09-25 17:12:17'],
            ['cf' => 'MRNCLL17B56C136P', 'email' => 'giggina79@yahoo.it', 'nome' => 'Camilla', 'cognome' => 'Murianni', 'sesso' => 'F', 'cellulare' => '3475284282', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'TRSFNC10R59B180K', 'email' => 'Gipy1977@gmail.com', 'nome' => 'Francesca', 'cognome' => 'Taurisano', 'sesso' => 'M', 'cellulare' => '3476466586', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NNCGTT09E48E205L', 'email' => 'giudyanny2@gmail.com', 'nome' => 'Giuditta', 'cognome' => 'Annicchiarico', 'sesso' => 'F', 'cellulare' => '3917492880', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LTNMRN12H48L049Z', 'email' => 'giuseppe.latanza1080@gmail.com', 'nome' => 'Miriana', 'cognome' => 'Latanza', 'sesso' => 'F', 'cellulare' => '3457925764', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CRCNCH09R64E986D', 'email' => 'giuseppecarucci86@libero.it', 'nome' => 'Annachiara', 'cognome' => 'Carucci', 'sesso' => 'F', 'cellulare' => '3791872062', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NSTGLI15D54D761S', 'email' => 'grazia.miccoli@tim.it', 'nome' => 'Giulia', 'cognome' => 'Anastasia', 'sesso' => 'F', 'cellulare' => '3381989413', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'FMRGZN78L71E986D', 'email' => 'grazianafumarola5@gmail.com', 'nome' => 'Graziana', 'cognome' => 'Fumarola', 'sesso' => 'F', 'cellulare' => '3479754814', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'GRNFRC14C50E205A', 'email' => 'guarini.roberto@virgilio.it', 'nome' => 'Federica', 'cognome' => 'Guarini', 'sesso' => 'F', 'cellulare' => '3471338256', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'DVNSRA13D64E205R', 'email' => 'imma.dib85@gmail.com', 'nome' => 'Sara', 'cognome' => 'Di Venere', 'sesso' => 'F', 'cellulare' => '3926876830', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NCCSFO17S62D761Y', 'email' => 'jasminedecarolis7@gmail.com', 'nome' => 'Sofia', 'cognome' => 'Nacci', 'sesso' => 'F', 'cellulare' => '3892316058', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MSLNTA13C46E205M', 'email' => 'katiaarcadio@gmail.com', 'nome' => 'Anita', 'cognome' => 'Masella', 'sesso' => 'F', 'cellulare' => '3485755021', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MNCGLI15L53Z404O', 'email' => 'lesliegoldsmid@gmail.com', 'nome' => 'Giulia', 'cognome' => 'Manica', 'sesso' => 'F', 'cellulare' => '3517799533', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'DMCLTZ01M53E205Y', 'email' => 'letiziada5@gmail.com', 'nome' => 'Letizia', 'cognome' => 'D\'Amicis', 'sesso' => 'F', 'cellulare' => '3271385620', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'DNGLLN93B50E205L', 'email' => 'liliana_deangelis@libero.it', 'nome' => 'Liliana', 'cognome' => 'De Angelis', 'sesso' => 'F', 'cellulare' => '3405058592', 'attivo' => 1, 'created_at' => '2025-10-01 17:31:29'],
            ['cf' => 'PLSLRN03P48E205G', 'email' => 'lorena.peluso.003@icloud.com', 'nome' => 'Lorena', 'cognome' => 'Peluso', 'sesso' => 'F', 'cellulare' => '3283056251', 'attivo' => 1, 'created_at' => '2025-10-17 12:13:49'],
            ['cf' => 'NNCSAI12E55E205M', 'email' => 'loryenastefani@gmail.com', 'nome' => 'Asia', 'cognome' => 'Annicchiarico', 'sesso' => 'F', 'cellulare' => '3401987096', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'BNFLCU65R51D761R', 'email' => 'lucia065@tiscali.it', 'nome' => 'Lucia', 'cognome' => 'Bonfrate', 'sesso' => 'F', 'cellulare' => '3292968319', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'RCCGGM07L44E017S', 'email' => 'luciafinocchiaro1@gmail.com', 'nome' => 'Giorgia', 'cognome' => 'Ricca', 'sesso' => 'F', 'cellulare' => '3471924304', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CSMLCL06S67Z604D', 'email' => 'lucila.cosmani@gmail.com', 'nome' => 'Lucila', 'cognome' => 'Cosmani', 'sesso' => 'F', 'cellulare' => '3922868202', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'DNCLNU06C48E205D', 'email' => 'lunadanucci@gmail.com', 'nome' => 'Luna', 'cognome' => 'Danucci', 'sesso' => 'F', 'cellulare' => '3924007469', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LPUDZN07H48E205O', 'email' => 'lupoluigi72@gmail.com', 'nome' => 'Domiziana', 'cognome' => 'Lupo', 'sesso' => 'F', 'cellulare' => '3921865824', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CRTCRL16R67L049B', 'email' => 'macelleriacoretti@gmail.com', 'nome' => 'Charlotte', 'cognome' => 'Coretti', 'sesso' => 'F', 'cellulare' => '3518829041', 'attivo' => 1, 'created_at' => '2025-08-27 12:05:36'],
            ['cf' => 'LAOLCA11P60D761Q', 'email' => 'marcello_lisa25@yahoo.it', 'nome' => 'Alice', 'cognome' => 'Alò', 'sesso' => 'F', 'cellulare' => '3206452148', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MSLCST18T51L049H', 'email' => 'marcorizzo2412@gmail.com', 'nome' => 'Cristel', 'cognome' => 'RIZZO MASELLA', 'sesso' => 'F', 'cellulare' => '3453545491', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'DLAMEI16L43E205N', 'email' => 'Mari_mig@libero.it', 'nome' => 'Emi', 'cognome' => 'D\'Alo\'', 'sesso' => 'F', 'cellulare' => '3497107186', 'attivo' => 1, 'created_at' => '2025-10-02 09:39:04'],
            ['cf' => 'STSMCH07R55E205T', 'email' => 'mariachiarastasi2007@gmail.com', 'nome' => 'Mariachiara', 'cognome' => 'Stasi', 'sesso' => 'F', 'cellulare' => '3472577628', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LPUCSM11A07B180M', 'email' => 'mariafontano80@gmail.com', 'nome' => 'Cosimo', 'cognome' => 'Lupo', 'sesso' => 'M', 'cellulare' => '3288356087', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'BCCMRP56S43E882Q', 'email' => 'mariapia.buccolieri@gmail.com', 'nome' => 'Mariapia', 'cognome' => 'Buccolieri', 'sesso' => 'F', 'cellulare' => '3474489352', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'NNCLSN16B48E205V', 'email' => 'marinellaquaranta74@gmail.com', 'nome' => 'Alessandra', 'cognome' => 'Annicchiarico', 'sesso' => 'F', 'cellulare' => '3405100136', 'attivo' => 1, 'created_at' => '2025-09-27 10:28:54'],
            ['cf' => 'MRNPRZ83L09L049F', 'email' => 'marino.patrizio83@libero.it', 'nome' => 'Patrizio', 'cognome' => 'Marino', 'sesso' => 'M', 'cellulare' => '3491941186', 'attivo' => 1, 'created_at' => '2025-10-24 19:04:41'],
            ['cf' => 'MRTGRL98M55L049U', 'email' => 'MartinelliGabriella@outlook.it', 'nome' => 'Gabriella', 'cognome' => 'Martinelli', 'sesso' => 'F', 'cellulare' => '3203339182', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'BNCCLN16T44D761S', 'email' => 'merollasandy@gmail.com', 'nome' => 'Caroline', 'cognome' => 'Bianco', 'sesso' => 'F', 'cellulare' => '3883018679', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'VNZMHL91D22E205O', 'email' => 'mi.chy.91@hotmail.it', 'nome' => 'michele', 'cognome' => 'venza', 'sesso' => 'M', 'cellulare' => '3408730983', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'GSTMHL83E13E205S', 'email' => 'mikelegg@hotmail.it', 'nome' => 'Michele', 'cognome' => 'Giustizieri', 'sesso' => 'M', 'cellulare' => '3409295364', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'BNGCSM60M03D761Q', 'email' => 'mimmobuong@live.it', 'nome' => 'Cosimo', 'cognome' => 'Buongiorno', 'sesso' => 'M', 'cellulare' => '3393810935', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MSCNNA65A46B808X', 'email' => 'musanna65@hotmail.it', 'nome' => 'Anna', 'cognome' => 'Musacchio', 'sesso' => 'F', 'cellulare' => '3286004428', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MNTGIO13P65E205B', 'email' => 'myagioia@gmail.com', 'nome' => 'Gioia', 'cognome' => 'Montanaro', 'sesso' => 'F', 'cellulare' => '3891404676', 'attivo' => 1, 'created_at' => '2025-09-30 15:08:57'],
            ['cf' => 'SCLGLI18S49L049O', 'email' => 'n.veronica29@hotmail.it', 'nome' => 'Giulia', 'cognome' => 'Scialpi', 'sesso' => 'F', 'cellulare' => '3396817300', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'DNPGNZ82P01L049F', 'email' => 'neclepsio@gmail.com', 'nome' => 'Ignazio', 'cognome' => 'Di Napoli', 'sesso' => 'M', 'cellulare' => '3200220542', 'attivo' => 1, 'created_at' => '2025-09-23 16:12:39'],
            ['cf' => 'VRSMNL16T70L049R', 'email' => 'orsolamastro80@gmail.com', 'nome' => 'Emanuela', 'cognome' => 'Aversa', 'sesso' => 'F', 'cellulare' => '3471455738', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'QRNDLL15B53L049L', 'email' => 'palmadurante92@gmail.com', 'nome' => 'Dalila', 'cognome' => 'Quaranta', 'sesso' => 'F', 'cellulare' => '3404614235', 'attivo' => 1, 'created_at' => '2025-09-24 08:40:00'],
            ['cf' => 'TNLPLM81H47E205M', 'email' => 'palmatinella@gmail.com', 'nome' => 'Palma', 'cognome' => 'Tinella', 'sesso' => 'F', 'cellulare' => '3281542277', 'attivo' => 1, 'created_at' => '2025-10-15 16:59:38'],
            ['cf' => 'GNZLVC19R58D761L', 'email' => 'palmieridesire47@gmail.com', 'nome' => 'Ludovica', 'cognome' => 'Ignazio', 'sesso' => 'F', 'cellulare' => '3500430375', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'FNNGAI16H56E205W', 'email' => 'pamelaf@smilerepair.it', 'nome' => 'Gaia', 'cognome' => 'Fanni', 'sesso' => 'F', 'cellulare' => '3282856907', 'attivo' => 1, 'created_at' => '2025-09-30 15:04:31'],
            ['cf' => 'DMTDNS15D57E205A', 'email' => 'paoladenisenicolas@gmail.com', 'nome' => 'Denise', 'cognome' => 'di Motoli', 'sesso' => 'F', 'cellulare' => '3488138489', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'PSCCRS85A08L219G', 'email' => 'pascalchristian85@gmail.com', 'nome' => 'Christian', 'cognome' => 'Pascale', 'sesso' => 'M', 'cellulare' => '3924938553', 'attivo' => 1, 'created_at' => '2025-10-26 11:35:15'],
            ['cf' => 'sprrfl11m46e205g', 'email' => 'raffaellaspartano138@gmail.com', 'nome' => 'raffaella', 'cognome' => 'spartano', 'sesso' => 'F', 'cellulare' => '3500106703', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'GNFLRA17L66D761O', 'email' => 'remigio.gianfreda@libero.it', 'nome' => 'Laura', 'cognome' => 'Gianfreda', 'sesso' => 'F', 'cellulare' => '3462132741', 'attivo' => 1, 'created_at' => '2025-10-04 16:24:09'],
            ['cf' => 'RBZMLY08R70B180D', 'email' => 'ribezzoemily5@gmail.com', 'nome' => 'Emily', 'cognome' => 'Ribezzo', 'sesso' => 'F', 'cellulare' => '3713659665', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'SCHFCN16R68L049E', 'email' => 'roberto.donnaloia@gmail.com', 'nome' => 'Francesca', 'cognome' => 'Schiano Lomoriello', 'sesso' => 'F', 'cellulare' => '3477971604', 'attivo' => 1, 'created_at' => '2025-09-17 09:22:13'],
            ['cf' => 'PTNRRT03A25L049N', 'email' => 'robertopetani67@gmail.com', 'nome' => 'Roberto', 'cognome' => 'Petani', 'sesso' => 'M', 'cellulare' => '3485852308', 'attivo' => 1, 'created_at' => '2025-09-24 05:44:05'],
            ['cf' => 'QRNRNN79P65E205H', 'email' => 'rosannerox11@gmail.com', 'nome' => 'ROSANNA', 'cognome' => 'QUARANTA', 'sesso' => 'F', 'cellulare' => '3395773103', 'attivo' => 1, 'created_at' => '2025-09-10 15:55:20'],
            ['cf' => 'MGGSVT70M13B180J', 'email' => 'salvatore_maggiore@tiscali.it', 'nome' => 'Salvatore', 'cognome' => 'Maggiore', 'sesso' => 'M', 'cellulare' => '3313701747', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LTNSYR07B48E205Z', 'email' => 'samyra.latanza@liceomoscati.edu.it', 'nome' => 'samyra', 'cognome' => 'latanza', 'sesso' => 'F', 'cellulare' => '3290325479', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'DNNPTT12B62E205T', 'email' => 'santorocarmen@virgilio.it', 'nome' => 'Deanna', 'cognome' => 'Prettico', 'sesso' => 'F', 'cellulare' => '3384283833', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'RGGPTR62A06E986A', 'email' => 'scuolaguida90@gmail.com', 'nome' => 'Pietro', 'cognome' => 'Ruggiero', 'sesso' => 'M', 'cellulare' => '3714178974', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'VNCSLV74R49H501P', 'email' => 'silviavinci@virgilio.it', 'nome' => 'Ginevra', 'cognome' => 'Calzolaio', 'sesso' => 'F', 'cellulare' => '3495649337', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'MNTSFO11P66E205H', 'email' => 'sofiaurora12@gmail.com', 'nome' => 'Sofia', 'cognome' => 'Minetola', 'sesso' => 'F', 'cellulare' => '3294746318', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'SPRGAI12C71E205I', 'email' => 'spartanomichele@gmail.com', 'nome' => 'Gaia', 'cognome' => 'Spartano', 'sesso' => 'F', 'cellulare' => '3773701151', 'attivo' => 1, 'created_at' => '2025-09-24 17:22:43'],
            ['cf' => 'TDRCHR19L66L049C', 'email' => 'terranovangela@gmail.com', 'nome' => 'Angela', 'cognome' => 'Terranova', 'sesso' => 'F', 'cellulare' => '3491674303', 'attivo' => 1, 'created_at' => '2025-09-14 14:44:35'],
            ['cf' => '2132132123113123', 'email' => 'testutente@emanuelerosato.com', 'nome' => 'Emanuele', 'cognome' => 'Rosato', 'sesso' => 'M', 'cellulare' => '1232133212', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LBNSRA17C42A662U', 'email' => 'tizianarochira71@gmail.com', 'nome' => 'Sara', 'cognome' => 'Albano', 'sesso' => 'F', 'cellulare' => '3477664654', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'RGGNNA15M52E205N', 'email' => 'tizianascatigna8@gmail.com', 'nome' => 'Anna', 'cognome' => 'Ruggieri', 'sesso' => 'F', 'cellulare' => '3275510702', 'attivo' => 1, 'created_at' => '2025-09-29 15:21:05'],
            ['cf' => 'MRNSMR14M55E205O', 'email' => 'tizianavestita1991@gmail.com', 'nome' => 'Isia Maria', 'cognome' => 'Marinelli', 'sesso' => 'F', 'cellulare' => '3491066819', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'BCCTNO76B08E205U', 'email' => 'toni.bucci@alice.it', 'nome' => 'TONI', 'cognome' => ' BUCCI', 'sesso' => 'M', 'cellulare' => '3470046674', 'attivo' => 1, 'created_at' => '2025-09-04 17:48:33'],
            ['cf' => 'FRNVLR89C41E205V', 'email' => 'val9far@hotmail.it', 'nome' => 'Valeria', 'cognome' => 'Farina', 'sesso' => 'F', 'cellulare' => '3277060144', 'attivo' => 1, 'created_at' => '2025-09-23 15:02:36'],
            ['cf' => 'LZZLCA06T67A048G', 'email' => 'valeria.abbamonte@gmail.com', 'nome' => 'Alice', 'cognome' => 'Liuzzi', 'sesso' => 'F', 'cellulare' => '3337432908', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'SNTVRM14T64E205F', 'email' => 'valeriaamely24@gmail.com', 'nome' => 'Amely', 'cognome' => 'Santoro', 'sesso' => 'F', 'cellulare' => '3272488013', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'LNEMSS15L47D761U', 'email' => 'vanessa96miccoli@gmail.com', 'nome' => 'Melissa', 'cognome' => 'Leone', 'sesso' => 'F', 'cellulare' => '3775962331', 'attivo' => 1, 'created_at' => '2025-07-29 21:43:32'],
            ['cf' => 'CSTLNZ14L07L049Z', 'email' => 'vitocasto81@gmail.com', 'nome' => 'LORENZO', 'cognome' => 'CASTO', 'sesso' => 'M', 'cellulare' => '3312418327', 'attivo' => 1, 'created_at' => '2025-09-24 17:40:32'],
            ['cf' => 'VLPMRC86E18A662W', 'email' => 'volpemarco86@gmail.com', 'nome' => 'Martina', 'cognome' => 'Volpe', 'sesso' => 'F', 'cellulare' => '3393220977', 'attivo' => 1, 'created_at' => '2025-10-22 09:15:24'],
            ['cf' => 'CLANCL13L56E205H', 'email' => 'vurselli@gmail.com', 'nome' => 'Angelica', 'cognome' => 'Calò', 'sesso' => 'F', 'cellulare' => '3494423454', 'attivo' => 1, 'created_at' => '2025-09-25 17:11:53'],
        ];
    }
}
