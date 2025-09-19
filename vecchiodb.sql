-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Creato il: Set 19, 2025 alle 11:58
-- Versione del server: 11.8.3-MariaDB-log
-- Versione PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u361938811_dds07`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `active_sessions`
--

CREATE TABLE `active_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_at` timestamp NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `logout_at` timestamp NULL DEFAULT NULL,
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`)),
  `location_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`location_info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `admin_backups`
--

CREATE TABLE `admin_backups` (
  `id` int(11) NOT NULL,
  `backup_name` varchar(255) NOT NULL,
  `backup_type` enum('manual','scheduled','automatic') DEFAULT 'manual',
  `status` enum('pending','running','completed','failed') DEFAULT 'pending',
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `tables_included` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tables_included`)),
  `compression_type` varchar(20) DEFAULT 'gzip',
  `encryption_enabled` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `checksum` varchar(64) DEFAULT NULL,
  `retention_days` int(11) DEFAULT 30,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `log_type` enum('info','warning','error','security','audit','system') DEFAULT 'info',
  `action` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `resource_type` varchar(50) DEFAULT NULL,
  `resource_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `message` text DEFAULT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'low',
  `status` enum('success','failed','pending') DEFAULT 'success',
  `execution_time` float DEFAULT NULL,
  `stack_trace` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_encrypted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `category`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_encrypted`, `created_at`, `updated_at`, `updated_by`) VALUES
(1, 'system', 'maintenance_mode', 'false', 'boolean', 'Attiva modalità manutenzione', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(2, 'system', 'backup_retention_days', '30', 'number', 'Giorni di conservazione backup', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(3, 'system', 'auto_backup_enabled', 'true', 'boolean', 'Backup automatico attivato', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(4, 'system', 'auto_backup_time', '02:00', 'string', 'Orario backup automatico', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(5, 'system', 'max_upload_size', '10485760', 'number', 'Dimensione massima upload in bytes', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(6, 'system', 'session_timeout', '7200', 'number', 'Timeout sessione in secondi', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(7, 'system', 'max_login_attempts', '5', 'number', 'Tentativi di login massimi', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(8, 'system', 'lockout_duration', '900', 'number', 'Durata blocco account in secondi', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(9, 'email', 'smtp_enabled', 'false', 'boolean', 'SMTP abilitato', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(10, 'email', 'smtp_host', '', 'string', 'Host SMTP', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(11, 'email', 'smtp_port', '587', 'number', 'Porta SMTP', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(12, 'email', 'smtp_username', '', 'string', 'Username SMTP', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(13, 'email', 'smtp_password', '', 'string', 'Password SMTP (crittografata)', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(14, 'email', 'from_email', 'noreply@danielsdanceschool.com', 'string', 'Email mittente predefinita', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(15, 'email', 'from_name', 'Daniel\'s Dance School', 'string', 'Nome mittente predefinito', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(16, 'security', 'enable_audit_log', 'true', 'boolean', 'Log audit attivato', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(17, 'security', 'password_min_length', '8', 'number', 'Lunghezza minima password', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(18, 'security', 'password_require_special', 'true', 'boolean', 'Richiedi caratteri speciali in password', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(19, 'security', 'two_factor_enabled', 'false', 'boolean', 'Autenticazione a due fattori', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(20, 'ui', 'items_per_page', '20', 'number', 'Elementi per pagina nelle tabelle', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(21, 'ui', 'theme_color', '#007bff', 'string', 'Colore tema principale', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(22, 'ui', 'logo_url', '/assets/img/logo.png', 'string', 'URL logo', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL),
(23, 'ui', 'organization_name', 'Daniel\'s Dance School', 'string', 'Nome organizzazione', 0, '2025-08-22 12:20:47', '2025-08-22 12:20:47', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `cartella`
--

CREATE TABLE `cartella` (
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `corso`
--

CREATE TABLE `corso` (
  `id` int(3) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` text NOT NULL,
  `livello` varchar(50) DEFAULT NULL COMMENT 'Livello del corso (Principiante, Intermedio, Avanzato)',
  `durata` varchar(100) DEFAULT NULL COMMENT 'Durata del corso (es: 3 mesi, 10 lezioni)',
  `prezzo` decimal(10,2) DEFAULT NULL COMMENT 'Prezzo del corso in euro',
  `immagine` varchar(255) DEFAULT NULL COMMENT 'Path relativo immagine corso',
  `attivo` tinyint(1) DEFAULT 1 COMMENT 'Corso attivo/inattivo',
  `data_creazione` timestamp NULL DEFAULT current_timestamp() COMMENT 'Data creazione corso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `corso`
--

INSERT INTO `corso` (`id`, `nome`, `descrizione`, `livello`, `durata`, `prezzo`, `immagine`, `attivo`, `data_creazione`) VALUES
(1, 'DANZE STANDARD', 'I 5 balli (valzer inglese, tango, valzer viennese, slow fox e quickstep) sono una fusione di eleganza, grinta e tecnica. Gli abiti scintillanti delle dame e il frack elegante del cavaliere le rendono le danze di coppia per eccellenza adatte a tutte le età.\r\n', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(2, 'DANZE LATINOAMERICANE', 'I 5 balli (samba, cha cha, rumba, paso doble e jive) trasportano i ballerini su ritmi travolgenti, grintosi e sensuali. Colori e forme sinuose sono le caratteristiche di chi si avvicina in qualsiasi momento a queste danze di coppia.', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(3, 'LATIN STYLE', 'Il percorso agonistico individuale adatto a chi non vuole rinunciare alla sensualità e alla vivacità  delle danze latino americane.', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(4, 'HIP HOP E BREAK DANCE', 'Arte, cultura, fisicità: un mix di elementi che rendono queste danze ideali a tutti gli atleti che, in sala o per strada, vogliono esibirsi con tecnica accattivante e coinvolgente.\r\n', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(5, 'AERIAL DANCE ', 'Derivante dalla cultura circense attraverso tessuti, corde e cerchi gli atleti svolgono evoluzioni e figurazione sospesi in aria: con forza e concentrazione i danzatori disegnano nell’aria coreografie mozzafiato e ricche di suspance.\r\n', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(6, 'FITNESS CORPO LIBERO', 'Attività fisica che unisce parte aerobica e muscolare a coreografie musicali. Per chi vuole dare tono e spensieratezza alla quotidianità.\r\n', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(7, 'LISCIO E BALLO DA SALA', 'La storia delle danze di coppia da balera e di gara che aprono la strada a tutti coloro che con divertimento vogliono intraprendere uno studio mirato nel mondo della danza sportiva.', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(8, 'CARAIBICI', 'Aggregazione e passione si uniscono a ritmo di salsa, bachata, merengue e kizomba: tutto quello che serve per avvicinarsi al mondo del ballo in qualsiasi momento e a qualsiasi livello. Non importa se in coppia o senza partner: ce n’è per tutti!\r\n', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(9, 'BALLI DI GRUPPO', 'Sempre alla moda e con uno sguardo al passato, il corso di balli di gruppo è un intramontabile soluzione per chi ha voglia di divertirsi in gruppo.\r\n', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(10, 'BABY DANCE', 'Corso adatto a bimbi da 3 a 6 anni: la lezione si basa sull’approccio base alle varie discipline della danza sportiva. Si alternano in maniera piacevole momenti di gioco, danza e socialità.\r\n', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(11, 'DANZA PARALIMPICA', 'Avviamento alla danza di livello agonistico per atleti con inabilità fisica, sensoriale e cognitiva.', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(12, 'ISTRUTTORE', 'Centro studi di formazione per il conseguimento di titoli all’insegnamento della danza sportiva.', NULL, NULL, NULL, NULL, 1, '2025-08-22 12:20:16'),
(23, 'saddsa', 'asdsda', 'Principiante', '1', 1.00, 'uploads/corsi/corso_68b0c32f5fe64.png', 1, '2025-08-28 22:59:27');

-- --------------------------------------------------------

--
-- Struttura della tabella `documenti`
--

CREATE TABLE `documenti` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `nome_originale` varchar(255) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `categoria` enum('certificati','tessere','attestati','regolamenti','modulistica','contratti','ricevute','comunicazioni','altro') NOT NULL DEFAULT 'altro',
  `tipo_file` enum('pdf','doc','docx','jpg','jpeg','png') NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `visibilita` enum('pubblico','privato','admin_only') NOT NULL DEFAULT 'privato',
  `ruoli_accesso` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ruoli_accesso`)),
  `socio_id` int(11) DEFAULT NULL,
  `corso_id` int(11) DEFAULT NULL,
  `caricato_da` int(11) NOT NULL,
  `creato_il` timestamp NULL DEFAULT current_timestamp(),
  `modificato_il` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `download_count` int(11) DEFAULT 0,
  `ultimo_download` timestamp NULL DEFAULT NULL,
  `attivo` tinyint(1) DEFAULT 1,
  `verificato` tinyint(1) DEFAULT 0,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `note_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `documenti_categorie`
--

CREATE TABLE `documenti_categorie` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descrizione` text DEFAULT NULL,
  `icona` varchar(50) DEFAULT 'file',
  `colore` varchar(7) DEFAULT '#3498db',
  `ordine` int(11) DEFAULT 0,
  `attiva` tinyint(1) DEFAULT 1,
  `creata_il` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `documenti_categorie`
--

INSERT INTO `documenti_categorie` (`id`, `nome`, `descrizione`, `icona`, `colore`, `ordine`, `attiva`, `creata_il`) VALUES
(1, 'certificati', 'Certificati medici e altri certificati ufficiali', 'certificate', '#e74c3c', 0, 1, '2025-08-22 12:20:26'),
(2, 'tessere', 'Tessere associative e documenti di iscrizione', 'id-card', '#3498db', 0, 1, '2025-08-22 12:20:26'),
(3, 'attestati', 'Attestati di partecipazione e riconoscimenti', 'award', '#f39c12', 0, 1, '2025-08-22 12:20:26'),
(4, 'regolamenti', 'Regolamenti interni e documenti normativi', 'gavel', '#9b59b6', 0, 1, '2025-08-22 12:20:26'),
(5, 'modulistica', 'Moduli e documenti da compilare', 'file-alt', '#2ecc71', 0, 1, '2025-08-22 12:20:26'),
(6, 'contratti', 'Contratti e accordi', 'handshake', '#34495e', 0, 1, '2025-08-22 12:20:26'),
(7, 'ricevute', 'Ricevute di pagamento e fatture', 'receipt', '#16a085', 0, 1, '2025-08-22 12:20:26'),
(8, 'comunicazioni', 'Comunicazioni ufficiali e circolari', 'bullhorn', '#e67e22', 0, 1, '2025-08-22 12:20:26');

-- --------------------------------------------------------

--
-- Struttura della tabella `documenti_download_log`
--

CREATE TABLE `documenti_download_log` (
  `id` int(11) NOT NULL,
  `documento_id` int(11) NOT NULL,
  `utente_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `download_timestamp` timestamp NULL DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `evento`
--

CREATE TABLE `evento` (
  `id` int(50) NOT NULL,
  `data` varchar(8) NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `orario`
--

CREATE TABLE `orario` (
  `id` int(5) NOT NULL,
  `disciplina` varchar(50) NOT NULL,
  `lunedi` text NOT NULL,
  `martedi` text NOT NULL,
  `mercoledi` text NOT NULL,
  `giovedi` text NOT NULL,
  `venerdi` text NOT NULL,
  `sabato` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `orario`
--

INSERT INTO `orario` (`id`, `disciplina`, `lunedi`, `martedi`, `mercoledi`, `giovedi`, `venerdi`, `sabato`) VALUES
(90, 'Latin Style Base', '', '17:00', '', '17:00', '', ''),
(92, 'Standard', '20:30/22:00', '', '', '20:00/21:00', '', ''),
(93, 'Latini', '20:30/22:00', '', '19:00', '', '', ''),
(94, 'Social Dance \nM. Venza', '18:00', '', '18:00', '', '', ''),
(95, 'Social Dance\nS. Di Motoli', '21:00', '', '', '21:00', '', ''),
(98, 'Caraibici', '', '', '21:00/22:00', '', '21:00/22:00', ''),
(99, 'Hip Hop', '17:00', '', '', '', '17:00', ''),
(100, 'Liscio\nBallo da Sala', '', '', '19:00', '', '19:00', ''),
(101, 'Latin Style\nAgonisti', '20:30/22:00', '', '', '20:00', '', ''),
(103, 'Standard', '20:30/22:00', '', '', '21:00', '', ''),
(104, 'Aerial Dance', '18:00/22:00', '16:00/17:00\n18:00/19:00', '', '16:00/22:00', '', '17:00');

-- --------------------------------------------------------

--
-- Struttura della tabella `socio`
--

CREATE TABLE `socio` (
  `cf` varchar(16) NOT NULL,
  `email` varchar(50) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `sesso` varchar(1) NOT NULL,
  `password` varchar(32) NOT NULL,
  `corso` int(3) NOT NULL,
  `cellulare` varchar(10) NOT NULL,
  `attivo` tinyint(1) NOT NULL DEFAULT 0,
  `ruolo` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `email_verificata` tinyint(1) NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `socio`
--

INSERT INTO `socio` (`cf`, `email`, `nome`, `cognome`, `sesso`, `password`, `corso`, `cellulare`, `attivo`, `ruolo`, `created_at`, `updated_at`, `last_login`, `email_verificata`, `note`, `avatar`, `reset_token`, `reset_token_expire`) VALUES
('RCSGLI14R68L049K', 'adapiccoli@virgilio.it', 'Giulia', 'Arces', 'F', '30097cdc9253d551aeae82d89afac801', 5, '3498161980', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('SLBRRT18A61D761E', 'adrianaalo82@gmail.com', 'Roberta ', 'Siliberto ', 'F', '0f4d971ac1b8825856354afc0f4583e8', 5, '3496287930', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CVLCLT16R62D761C', 'Albertovaio@yahoo.it', 'Carlotta ', 'Cavallo', 'F', '9a1b081b9893e42615365382dd391e18', 1, '+393394965', 1, 'user', '2025-09-14 15:14:02', '2025-09-14 15:16:27', NULL, 0, NULL, NULL, NULL, NULL),
('NCCRRA20S56E986H', 'alessandronacci2021@gmail.com', 'Aurora ', 'Nacci ', 'F', '9c62fa1ad0424f4788a7e6da6db7d699', 3, '3892316058', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('FRNLSN99S27E205N', 'alessandrovito.fornaro@gmail.com', 'Alessandro Vito ', 'Fornaro', 'M', 'fdeefb9533668ed82a288eccb68ff407', 8, '3271383525', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('RDLMLY16S43D761G', 'alessiadurante32@gmail.com', 'Emily', 'Radulescu', 'F', '484a714c11a77d0bb7eb14270f90059c', 2, '3755660547', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GSTLCA09C58L049J', 'alicegiustizieri09@gmail.com', 'Alice', 'Giustizieri', 'F', '053c54ef622db0508ad2ae3b51957280', 5, '3402867992', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('ZZZZZZZZZZZZZZZZ', 'andreafilomeno@yahoo.it', 'Andrea', 'Filomeno', 'M', 'e3255f5d628b75895bc81dc4cb51624a', 2, '3333333333', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GFFMHS10D16B180J', 'andreeazima1@gmail.com', 'Mathias ', 'Goffredo ', 'M', 'c47c5237583400c6c13b6423aa750cdb', 1, '3804654090', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('NDRNTN85E21E205Y', 'andrianiantonio2105@gmail.com', 'Antonio', 'Andriani ', 'M', 'e2a4e5e3c78a3fa960e49faa4f4df554', 1, '3247458957', 1, 'user', '2025-09-14 14:14:19', '2025-09-14 15:23:27', NULL, 0, NULL, NULL, NULL, NULL),
('RLNNGL79P54E205N', 'angelaorlando7926@gmail.com', 'Angela', 'Orlando ', 'F', '3fefb04a4cef50d4d56339da9574fa96', 5, '3491959973', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CRLNGL63C11C741I', 'angelo.caroli1963@libero.it', 'Angelo', 'Caroli', 'M', 'ecbbe1ac8d04790502bc1c52ebe25469', 1, '3287489422', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('BCCMTN11R48L049L', 'annacaramia2022@libero.it', 'Martina ', 'Bucci', 'F', 'dda435eb7280c456ddce698fc819c228', 2, '3208593593', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MNGCLT17E50D761P', 'annarita.romano1@icloud.com', 'Carlotta ', 'Manigrasso', 'F', '1070e5189ef3802212d6eefbdee8bc22', 5, '3398171047', 1, 'user', '2025-09-14 15:32:39', '2025-09-14 15:32:44', NULL, 0, NULL, NULL, NULL, NULL),
('NNCMLS84R57E205R', 'Annicchiaricomarialuisa@gmail.com', 'Elisa ', 'Dedja', 'F', 'c1f9ef60d2940a833ffbb386739d69cd', 3, '3282732529', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MNDRRA16M62D761U', 'antonellalisi88@gmail.com', 'Aurora ', 'Mondelli ', 'F', 'f2d8a6344e260de386021de9a4d1b32d', 2, '3292930370', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LPURRA11A47B180P', 'apruzzese_maria@libero.it', 'Aurora ', 'Lupo', 'F', '4df0d649b83f71f3898faad8824d3ae3', 1, '3288356087', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('RGSNTN68B49E986G', 'arianna.raguso68@gmail.com', 'Arianna ', 'Raguso ', 'F', '4d787b72312d0d738e9df1372fb34b8c', 1, '3296313699', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LCRSMN13C04C136W', 'ariannacaiazzo@libero.it', 'Simone', 'Lacorte', 'M', '2c1f7da7273658d0170905b9e1575c6c', 4, '3490611640', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('NNCNGL68B42C424B', 'atelierannicchiarico@libero.it ', 'Angela ', 'Annicchiarico ', 'F', '6429f3262b6c5eae6dcc1dc5c590c90d', 1, '3495878318', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CRRPPZ76C54E205H', 'aziacarrieri@gmail.com', 'Azia', 'Carrieri', 'F', '687173c27c3ab28b3d1c06da3358e186', 4, '3470368528', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('BNCLSS16P61D761W', 'biancoand1973@gmail.com', 'Alessia', 'Bianco ', 'F', 'cf8abb589b85bb921b8f076116581d1d', 3, '3393138837', 1, 'user', '2025-09-17 07:23:35', '2025-09-17 07:24:08', NULL, 0, NULL, NULL, NULL, NULL),
('RSTGDI14R54E986F', 'caliandrolucrezia1978@gmail.com', 'Giada', 'Rosato', 'F', '4791158baeb5fef6c0d611a309f1b9ed', 5, '3270030094', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CMPFNC08B54E205O', 'campanellaf74@gmail.com', 'Francesca', 'Campanella', 'F', '118adb251c6bf9470d2f362312707c86', 2, '3917622664', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('NSIMRP14P63L049T', 'carmela.galeone@libero.it', 'Maria pia ', 'Nisi', 'F', 'acd82c7ca6e512db0535e9c3ebca1ee6', 3, '3248293377', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LPPCML55R46C129T', 'carmelalippolis55@gmail.com', 'Carmela', 'Lippolis', 'F', '25d55ad283aa400af464c76d713c07ad', 9, '3284109388', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MNDLRZ12B42L049A', 'chefvip@hotmail.it', 'Lucrezia ', 'Amendola', 'F', '7543857aa5f83b936e019ce676bd294a', 3, '3472633398', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MDCGAI19E62E986O', 'cinziaaprile80@gmail.com', 'Gaia ', 'Medici ', 'F', 'b1b6d44125ccfc6035bd0c29e750583d', 2, '3401827844', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LNTCRI65B23E205W', 'ciro.lenti@hotmail.com', 'Ciro', 'Lenti ', 'M', 'dfff97663e4575acebb396011d3aaaf4', 1, '3281582168', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('NSIGLI16M63H264J', 'ciro85@msn.com', 'Giulia', 'Nisi', 'F', 'e5b72f884503593d643c7cc5eee557a1', 5, '3496146091', 1, 'user', '2025-09-15 15:39:08', '2025-09-15 15:41:37', NULL, 0, NULL, NULL, NULL, NULL),
('LPUCRI72T26E205V', 'cirolupo1972@libero.it', 'Ciro', 'Lupo', 'M', '5f4ee049dc44297136ed46b3233d171a', 5, '3246931823', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LPUFRC14P64E205Q', 'cirolupo72@gmail.com', 'Federica', 'Lupo', 'F', '3033e12e03aa8e71408410401b6580ef', 5, '3246931823', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('BRTCLD06H61E205O', 'claudiabritt18@gmail.com', 'Claudia', 'Brittannico', 'F', '2003a1424aa1fb7164788574d286f88c', 5, '3926096049', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('TTNCLO08P61L049Y', 'cloeattanasi@gmail.com', 'CLOE', 'ATTANASI ', 'F', '19ded32be0088842f1ed76499bedbd12', 5, '+393203243', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('TRNCMS64E50E986P', 'comasiaturnone0@gmail.com', 'Comasia', 'Turnone', 'F', '6f744703c4b99dd39d0eb984e27aedef', 1, '3283619108', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CRSCSM74D62E205R', 'crescenziomimma4791@gmail.com', 'Cosima', 'Crescenzio', 'F', '075a61b53b2686c240205cf2d202a51e', 9, '3249891643', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CRSDNL83D51E205U', 'danielacre@alice.it', 'Daniela', 'Crescenzio', 'F', 'd51b3b9609a8e9f2d35c17d6cdeb65e5', 1, '3888517783', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('QRNRCH15H69E205N', 'daniele4017@gmail.com', 'Aurora Chiara ', 'Quaranta ', 'F', '111f49c6d38131bfc9679d467bbc6659', 5, '3479581796', 1, 'user', '2025-09-14 14:48:13', '2025-09-14 14:48:32', NULL, 0, NULL, NULL, NULL, NULL),
('CRVCRS13M55L049G', 'danielecarovigna@hotmail.it', 'Christel ', 'Carovigna ', 'F', '88da4b124ebc83d864aa25da387e1074', 2, '3468781015', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('QRNGRT18L65E986Z', 'el_quaranta@libero.it', 'Greta', 'Quaranta', 'F', '44960ef48e0171c3715f732db2f448eb', 5, '3479747969', 1, 'user', '2025-09-15 12:53:26', '2025-09-15 13:00:36', NULL, 0, NULL, NULL, NULL, NULL),
('sadsasdasdasadsa', 'emanuelerosato.com@gmail.com', 'asddsa', 'sasadsasda', 'M', 'bf709005906087dc1256bb4449d8774d', 1, 'sadsasasda', 1, 'admin', '2025-07-29 21:43:32', '2025-07-29 21:43:32', NULL, 0, NULL, NULL, NULL, NULL),
('CVLMNL11B14E205C', 'family01rty@gmail.com', 'Emmanuele ', 'Cavallo', 'M', 'ee35cf4c591ad812d74e0cf6f74a3c08', 1, '3290109867', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('PGLGAI14P55E205X', 'franciterranova1@gmail.com', 'Gaia', 'Pugliese ', 'F', 'ea799fedca074302aa181090ee32626a', 2, '3398909663', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('SCPFNC57M04E205J', 'fscappati@gmail.com', 'Franco', 'Scappati', 'M', '7396ce960f7289d780f25295755f24a4', 1, '3292041441', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LGRMNL11E59E205P', 'gennycaputo71@gmail.com', 'Manuela ', 'ligorio ', 'F', '6d1f94153ea98a39741a5ebd51c604a1', 5, '3494964899', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MRNCLL17B56C136P', 'giggina79@yahoo.it', 'Camilla ', 'Murianni ', 'F', '1a6b97414564cb725e2310d7ec4dca7d', 5, '3475284282', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('TRSFNC10R59B180K', 'Gipy1977@gmail.com', 'Francesca', 'Taurisano', 'M', 'ce10ff6683021256c87fe35554279eb1', 5, '3476466586', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('NNCGTT09E48E205L', 'giudyanny2@gmail.com', 'Giuditta', 'Annicchiarico ', 'F', '1bc6583ab1db680009137f8d4e9c7b56', 4, '3917492880', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LTNMRN12H48L049Z', 'giuseppe.latanza1080@gmail.com', 'Miriana', 'Latanza', 'F', '8ccc08a8eb24ade2b2ce1875147f3473', 1, '3457925764', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CRCNCH09R64E986D', 'giuseppecarucci86@libero.it', 'Annachiara ', 'Carucci ', 'F', '84d2ee7aaf768e0d23e02072face33a8', 1, '3791872062', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('NSTGLI15D54D761S', 'grazia.miccoli@tim.it', 'Giulia', 'Anastasia', 'F', '07a1278ed2575bb32663277f10ccec4e', 3, '3381989413', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('FMRGZN78L71E986D', 'grazianafumarola5@gmail.com', 'Graziana', 'Fumarola', 'F', '76893523a8565e5efeb42566e35cb2c6', 5, '3479754814', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GRNFRC14C50E205A', 'guarini.roberto@virgilio.it', 'Federica ', 'Guarini', 'F', '856db6b82524e39fc501bdedd4d70f06', 5, '3471338256', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('DVNSRA13D64E205R', 'imma.dib85@gmail.com', 'Sara', 'Di Venere', 'F', 'dc90aeec38b5e1bdb7b001081beaed56', 2, '3926876830', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('NCCSFO17S62D761Y', 'jasminedecarolis7@gmail.com', 'Sofia', 'Nacci', 'F', '9551c56c310c1529854eda17a1ee6304', 3, '3892316058', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MSLNTA13C46E205M', 'katiaarcadio@gmail.com', 'Anita', 'Masella', 'F', '5f73d3cc3f7dacca5b9cfbf42b30d77e', 5, '3485755021', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MNCGLI15L53Z404O', 'lesliegoldsmid@gmail.com', 'Giulia', 'Manica', 'F', 'cb6ec12d07287b685330a795613732e1', 4, '3517799533', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('DMCLTZ01M53E205Y', 'letiziada5@gmail.com', 'Letizia', 'D’Amicis ', 'F', 'cdfaa6b24a3012cc0af2f437a8fc16b3', 1, '3271385620', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('NNCSAI12E55E205M', 'loryenastefani@gmail.com', 'Asia', 'Annicchiarico', 'F', 'e7f0d10c90e8f8f48ffc61e1f59cecb1', 2, '3401987096', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('BNFLCU65R51D761R', 'lucia065@tiscali.it', 'Lucia', 'Bonfrate', 'F', 'b4863076d712555f655781ea95a1e5e1', 2, '3292968319', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('RCCGGM07L44E017S', 'luciafinocchiaro1@gmail.com', 'Giorgia ', 'Ricca ', 'F', 'c4715f313d712f6215c2730c32fcd661', 3, '3471924304', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CSMLCL06S67Z604D', 'lucila.cosmani@gmail.com', 'Lucila', 'Cosmani ', 'F', '8c7f4d0d193af1ede563f334dd3833c2', 5, '3922868202', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('DNCLNU06C48E205D', 'lunadanucci@gmail.com', 'Luna', 'Danucci', 'F', 'ab2e62db38fa9cdd54d41697c3c8d166', 4, '3924007469', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LPUDZN07H48E205O', 'lupoluigi72@gmail.com', 'Domiziana', 'Lupo', 'F', '6d91317463ff828c3f5e38d74f1c4f09', 2, '3921865824', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('CRTCRL16R67L049B', 'macelleriacoretti@gmail.com', 'Charlotte ', 'Coretti', 'F', 'ab2ccfc5341a5a7a57d9b8b19b24a3f7', 5, '3518829041', 1, 'user', '2025-08-27 12:05:36', '2025-08-27 12:06:10', NULL, 0, NULL, NULL, NULL, NULL),
('LAOLCA11P60D761Q', 'marcello_lisa25@yahoo.it', 'Alice', 'Alò', 'F', 'b97062e8a846aa6197e27ae484b04bc3', 2, '3206452148', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MSLCST18T51L049H', 'marcorizzo2412@gmail.com', 'Cristel', 'RIZZO MASELLA', 'F', '9bbcd746c10f9bb0ed61f78baea9283d', 2, '3453545491', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('STSMCH07R55E205T', 'mariachiarastasi2007@gmail.com', 'Mariachiara', 'Stasi', 'F', '4e97bb4d8617c08e8e8b1e098f6cca5e', 3, '3472577628', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LPUCSM11A07B180M', 'mariafontano80@gmail.com', 'Cosimo', 'Lupo', 'M', 'd8d92de35944bbaa6bae4fe076ad8ad1', 1, '3288356087', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('BCCMRP56S43E882Q', 'mariapia.buccolieri@gmail.com', 'Mariapia', 'Buccolieri', 'F', 'b3312281d1bf046998d9d82810d1897e', 9, '3474489352', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MRTGRL98M55L049U', 'MartinelliGabriella@outlook.it', 'Gabriella', 'Martinelli', 'F', '2e8486f9144d549006d3aa61d093cf92', 1, '3203339182', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('BNCCLN16T44D761S', 'merollasandy@gmail.com', 'Caroline', 'Bianco', 'F', 'e1c565c5b1da2a3b81712427d06f5b34', 10, '3883018679', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('VNZMHL91D22E205O', 'mi.chy.91@hotmail.it', 'michele', 'venza', 'M', 'af8868d0da63a36b8f45a188e212096b', 1, '3408730983', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GSTMHL83E13E205S', 'mikelegg@hotmail.it', 'Michele', 'Giustizieri', 'M', '95431e3bf1368501c834d7ee1ca5771e', 12, '3409295364', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('BNGCSM60M03D761Q', 'mimmobuong@live.it', 'Cosimo', 'Buongiorno', 'M', 'd5728a4d1bd40e7cb8400972e8d9536f', 2, '3393810935', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MSCNNA65A46B808X', 'musanna65@hotmail.it', 'Anna', 'Musacchio', 'F', '3e2ee90d3ad7d0491b75e307662fac56', 1, '3286004428', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('SCLGLI18S49L049O', 'n.veronica29@hotmail.it', 'Giulia', 'Scialpi', 'F', '3d17fdad115cad7792ddbdb9964e5504', 2, '3396817300', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('VRSMNL16T70L049R', 'orsolamastro80@gmail.com', 'Emanuela ', 'Aversa', 'F', 'c5c1751d2ab5b7b38a3e6e8a868880cd', 2, '3471455738', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('GNZLVC19R58D761L', 'palmieridesire47@gmail.com', 'Ludovica', 'Ignazio', 'F', '1a93da0ede5689fff409695bed7aa1cb', 3, '3500430375', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('DMTDNS15D57E205A', 'paoladenisenicolas@gmail.com', 'Denise', 'di Motoli', 'F', '94b49702d2099cc1f9934cca7cf9ffdb', 3, '3488138489', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('sprrfl11m46e205g', 'raffaellaspartano138@gmail.com', 'raffaella ', 'spartano', 'F', '582a7d4e64200b2d84fce2387f44d7d7', 1, '3500106703', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('RBZMLY08R70B180D', 'ribezzoemily5@gmail.com', 'Emily ', 'Ribezzo', 'F', '6b9b53b799cb84a452d6d3707376e33c', 2, '3713659665', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('SCHFCN16R68L049E', 'roberto.donnaloia@gmail.com', 'Francesca', 'Schiano Lomoriello', 'F', '5fc74b666c8e20fae94dd77c2086e822', 5, '3477971604', 1, 'user', '2025-09-17 09:22:13', '2025-09-17 09:40:36', NULL, 0, NULL, NULL, NULL, NULL),
('QRNRNN79P65E205H', 'rosannerox11@gmail.com', 'ROSANNA', 'QUARANTA', 'F', 'bf7b9385bfd0aff50d6dca9fced063e7', 9, '3395773103', 1, 'user', '2025-09-10 15:55:20', '2025-09-10 15:57:51', NULL, 0, NULL, NULL, NULL, NULL),
('MGGSVT70M13B180J', 'salvatore_maggiore@tiscali.it', 'Salvatore ', 'Maggiore', 'M', '91870505c25d6ae24b6190760bb9b99e', 5, '3313701747', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LTNSYR07B48E205Z', 'samyra.latanza@liceomoscati.edu.it', 'samyra', 'latanza', 'F', '8d9d101670a1d6f3584d6d7d63da7161', 2, '3290325479', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('DNNPTT12B62E205T', 'santorocarmen@virgilio.it', 'Deanna', 'Prettico ', 'F', 'ff67875f0b2f84d6165d106e7c5490f4', 2, '3384283833', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('RGGPTR62A06E986A', 'scuolaguida90@gmail.com', 'Pietro', 'Ruggiero', 'M', '8d22ad30ccab2efde4dc13dce0b0abd3', 1, '3714178974', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('VNCSLV74R49H501P', 'silviavinci@virgilio.it', 'Ginevra', 'Calzolaio', 'F', '8240740b7374406a29b9e45366c2591d', 13, '3495649337', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MNTSFO11P66E205H', 'sofiaurora12@gmail.com ', 'Sofia', 'Minetola', 'F', 'be2fec751a18879c7f3ec2b08f74a3c2', 1, '3294746318', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('TDRCHR19L66L049C', 'terranovangela@gmail.com', 'Angela', 'Terranova ', 'F', '97d1a7b71f77e5ddfb5e9a676a6ba001', 5, '3491674303', 1, 'user', '2025-09-14 14:44:35', '2025-09-14 14:46:00', NULL, 0, NULL, NULL, NULL, NULL),
('2132132123113123', 'testutente@emanuelerosato.com', 'Emanuele', 'Rosato', 'M', '975c1e62aef696dbc936fa44f0f781c0', 1, '1232133212', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LBNSRA17C42A662U', 'tizianarochira71@gmail.com', 'Sara', 'Albano ', 'F', '96abff1208ff899814db35279f01bd17', 5, '3477664654', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('MRNSMR14M55E205O', 'tizianavestita1991@gmail.com', 'Isia Maria', 'Marinelli', 'F', 'bba8c51c466fc859e3263408ec44662c', 5, '3491066819', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('BCCTNO76B08E205U', 'toni.bucci@alice.it', 'TONI', ' BUCCI', 'M', '321c1511823ca1c05b0193efef4305fc', 1, '3470046674', 1, 'user', '2025-09-04 17:48:33', '2025-09-04 17:49:15', NULL, 0, NULL, NULL, NULL, NULL),
('LZZLCA06T67A048G', 'valeria.abbamonte@gmail.com', 'Alice', 'Liuzzi', 'F', '0d91a75bad7652838ccc07d892972828', 5, '3337432908', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('SNTVRM14T64E205F', 'valeriaamely24@gmail.com', 'Amely', 'Santoro', 'F', '5c3460197502cb4badb8a8a2e919003b', 4, '3272488013', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL),
('LNEMSS15L47D761U', 'vanessa96miccoli@gmail.com', 'Melissa ', 'Leone', 'F', '4743b3e30afc71c892108b4e482db50a', 3, '3775962331', 1, 'user', '2025-07-29 21:43:32', NULL, NULL, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `sociocorso`
--

CREATE TABLE `sociocorso` (
  `cfSocio` varchar(16) NOT NULL,
  `idCorso` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `sociocorso`
--

INSERT INTO `sociocorso` (`cfSocio`, `idCorso`) VALUES
('2132132123113123', 1),
('BCCTNO76B08E205U', 1),
('CRCNCH09R64E986D', 1),
('CRLNGL63C11C741I', 1),
('CRSDNL83D51E205U', 1),
('CVLCLT16R62D761C', 1),
('CVLMNL11B14E205C', 1),
('DMCLTZ01M53E205Y', 1),
('GFFMHS10D16B180J', 1),
('LNTCRI65B23E205W', 1),
('LPUCSM11A07B180M', 1),
('LPURRA11A47B180P', 1),
('LTNMRN12H48L049Z', 1),
('MNTSFO11P66E205H', 1),
('MRTGRL98M55L049U', 1),
('MSCNNA65A46B808X', 1),
('NDRNTN85E21E205Y', 1),
('NNCNGL68B42C424B', 1),
('RGGPTR62A06E986A', 1),
('RGSNTN68B49E986G', 1),
('sadsasdasdasadsa', 1),
('SCPFNC57M04E205J', 1),
('sprrfl11m46e205g', 1),
('TRNCMS64E50E986P', 1),
('VNZMHL91D22E205O', 1),
('BCCMTN11R48L049L', 2),
('BNFLCU65R51D761R', 2),
('BNGCSM60M03D761Q', 2),
('CMPFNC08B54E205O', 2),
('CRVCRS13M55L049G', 2),
('CVLMNL11B14E205C', 2),
('DNNPTT12B62E205T', 2),
('DVNSRA13D64E205R', 2),
('LAOLCA11P60D761Q', 2),
('LPUDZN07H48E205O', 2),
('LTNSYR07B48E205Z', 2),
('MDCGAI19E62E986O', 2),
('MNDRRA16M62D761U', 2),
('MNTSFO11P66E205H', 2),
('MSLCST18T51L049H', 2),
('NNCSAI12E55E205M', 2),
('PGLGAI14P55E205X', 2),
('RBZMLY08R70B180D', 2),
('RDLMLY16S43D761G', 2),
('RGSNTN68B49E986G', 2),
('SCLGLI18S49L049O', 2),
('VRSMNL16T70L049R', 2),
('ZZZZZZZZZZZZZZZZ', 2),
('BNCLSS16P61D761W', 3),
('DMTDNS15D57E205A', 3),
('GNZLVC19R58D761L', 3),
('LNEMSS15L47D761U', 3),
('MNDLRZ12B42L049A', 3),
('NCCRRA20S56E986H', 3),
('NCCSFO17S62D761Y', 3),
('NNCMLS84R57E205R', 3),
('NSIMRP14P63L049T', 3),
('NSTGLI15D54D761S', 3),
('RCCGGM07L44E017S', 3),
('STSMCH07R55E205T', 3),
('VNCSLV74R49H501P', 3),
('CRRPPZ76C54E205H', 4),
('DNCLNU06C48E205D', 4),
('LCRSMN13C04C136W', 4),
('MNCGLI15L53Z404O', 4),
('NNCGTT09E48E205L', 4),
('SNTVRM14T64E205F', 4),
('BRTCLD06H61E205O', 5),
('CRTCRL16R67L049B', 5),
('CSMLCL06S67Z604D', 5),
('FMRGZN78L71E986D', 5),
('GRNFRC14C50E205A', 5),
('GSTLCA09C58L049J', 5),
('LBNSRA17C42A662U', 5),
('LGRMNL11E59E205P', 5),
('LPUCRI72T26E205V', 5),
('LPUFRC14P64E205Q', 5),
('LZZLCA06T67A048G', 5),
('MGGSVT70M13B180J', 5),
('MNGCLT17E50D761P', 5),
('MRNCLL17B56C136P', 5),
('MRNSMR14M55E205O', 5),
('MSLNTA13C46E205M', 5),
('NSIGLI16M63H264J', 5),
('QRNGRT18L65E986Z', 5),
('QRNRCH15H69E205N', 5),
('RCSGLI14R68L049K', 5),
('RLNNGL79P54E205N', 5),
('RSTGDI14R54E986F', 5),
('SCHFCN16R68L049E', 5),
('SLBRRT18A61D761E', 5),
('TDRCHR19L66L049C', 5),
('TRSFNC10R59B180K', 5),
('TTNCLO08P61L049Y', 5),
('FRNLSN99S27E205N', 8),
('BCCMRP56S43E882Q', 9),
('CRSCSM74D62E205R', 9),
('LPPCML55R46C129T', 9),
('QRNRNN79P65E205H', 9),
('BNCCLN16T44D761S', 10),
('ZZZZZZZZZZZZZZZZ', 11),
('GSTMHL83E13E205S', 12);

-- --------------------------------------------------------

--
-- Struttura della tabella `soci_new`
--

CREATE TABLE `soci_new` (
  `id` int(11) NOT NULL,
  `cf` varchar(16) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `sesso` enum('M','F') NOT NULL,
  `data_nascita` date DEFAULT NULL,
  `indirizzo` text DEFAULT NULL,
  `citta` varchar(100) DEFAULT NULL,
  `cap` varchar(5) DEFAULT NULL,
  `provincia` varchar(2) DEFAULT NULL,
  `cellulare` varchar(15) DEFAULT NULL,
  `telefono_fisso` varchar(15) DEFAULT NULL,
  `corso_principale` int(11) DEFAULT NULL,
  `attivo` tinyint(1) DEFAULT 1,
  `ruolo` enum('admin','user','istruttore') DEFAULT 'user',
  `note` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `email_verificata` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expire` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `soci_new`
--

INSERT INTO `soci_new` (`id`, `cf`, `email`, `password`, `nome`, `cognome`, `sesso`, `data_nascita`, `indirizzo`, `citta`, `cap`, `provincia`, `cellulare`, `telefono_fisso`, `corso_principale`, `attivo`, `ruolo`, `note`, `avatar`, `created_at`, `updated_at`, `last_login`, `email_verificata`, `reset_token`, `reset_token_expire`) VALUES
(1017, 'RCSGLI14R68L049K', 'adapiccoli@virgilio.it', '30097cdc9253d551aeae82d89afac801', 'Giulia', 'Arces', 'F', NULL, NULL, NULL, NULL, NULL, '3498161980', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1018, 'SLBRRT18A61D761E', 'adrianaalo82@gmail.com', '0f4d971ac1b8825856354afc0f4583e8', 'Roberta ', 'Siliberto ', 'F', NULL, NULL, NULL, NULL, NULL, '3496287930', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1019, 'NCCRRA20S56E986H', 'alessandronacci2021@gmail.com', '9c62fa1ad0424f4788a7e6da6db7d699', 'Aurora ', 'Nacci ', 'F', NULL, NULL, NULL, NULL, NULL, '3892316058', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1020, 'FRNLSN99S27E205N', 'alessandrovito.fornaro@gmail.com', 'fdeefb9533668ed82a288eccb68ff407', 'Alessandro Vito ', 'Fornaro', 'M', NULL, NULL, NULL, NULL, NULL, '3271383525', NULL, 8, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1021, 'RDLMLY16S43D761G', 'alessiadurante32@gmail.com', '484a714c11a77d0bb7eb14270f90059c', 'Emily', 'Radulescu', 'F', NULL, NULL, NULL, NULL, NULL, '3755660547', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1022, 'GSTLCA09C58L049J', 'alicegiustizieri09@gmail.com', '053c54ef622db0508ad2ae3b51957280', 'Alice', 'Giustizieri', 'F', NULL, NULL, NULL, NULL, NULL, '3402867992', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1023, 'ZZZZZZZZZZZZZZZZ', 'andreafilomeno@yahoo.it', 'e3255f5d628b75895bc81dc4cb51624a', 'Andrea', 'Filomeno', 'M', NULL, NULL, NULL, NULL, NULL, '3333333333', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1024, 'GFFMHS10D16B180J', 'andreeazima1@gmail.com', 'c47c5237583400c6c13b6423aa750cdb', 'Mathias ', 'Goffredo ', 'M', NULL, NULL, NULL, NULL, NULL, '3804654090', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1025, 'RLNNGL79P54E205N', 'angelaorlando7926@gmail.com', '3fefb04a4cef50d4d56339da9574fa96', 'Angela', 'Orlando ', 'F', NULL, NULL, NULL, NULL, NULL, '3491959973', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1026, 'CRLNGL63C11C741I', 'angelo.caroli1963@libero.it', 'ecbbe1ac8d04790502bc1c52ebe25469', 'Angelo', 'Caroli', 'M', NULL, NULL, NULL, NULL, NULL, '3287489422', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1027, 'BCCMTN11R48L049L', 'annacaramia2022@libero.it', 'dda435eb7280c456ddce698fc819c228', 'Martina ', 'Bucci', 'F', NULL, NULL, NULL, NULL, NULL, '3208593593', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1028, 'NNCMLS84R57E205R', 'Annicchiaricomarialuisa@gmail.com', 'c1f9ef60d2940a833ffbb386739d69cd', 'Elisa ', 'Dedja', 'F', NULL, NULL, NULL, NULL, NULL, '3282732529', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1029, 'MNDRRA16M62D761U', 'antonellalisi88@gmail.com', 'f2d8a6344e260de386021de9a4d1b32d', 'Aurora ', 'Mondelli ', 'F', NULL, NULL, NULL, NULL, NULL, '3292930370', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1030, 'LPURRA11A47B180P', 'apruzzese_maria@libero.it', '4df0d649b83f71f3898faad8824d3ae3', 'Aurora ', 'Lupo', 'F', NULL, NULL, NULL, NULL, NULL, '3288356087', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1031, 'RGSNTN68B49E986G', 'arianna.raguso68@gmail.com', '4d787b72312d0d738e9df1372fb34b8c', 'Arianna ', 'Raguso ', 'F', NULL, NULL, NULL, NULL, NULL, '3296313699', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1032, 'LCRSMN13C04C136W', 'ariannacaiazzo@libero.it', '2c1f7da7273658d0170905b9e1575c6c', 'Simone', 'Lacorte', 'M', NULL, NULL, NULL, NULL, NULL, '3490611640', NULL, 4, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1033, 'NNCNGL68B42C424B', 'atelierannicchiarico@libero.it ', '6429f3262b6c5eae6dcc1dc5c590c90d', 'Angela ', 'Annicchiarico ', 'F', NULL, NULL, NULL, NULL, NULL, '3495878318', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1034, 'CRRPPZ76C54E205H', 'aziacarrieri@gmail.com', '687173c27c3ab28b3d1c06da3358e186', 'Azia', 'Carrieri', 'F', NULL, NULL, NULL, NULL, NULL, '3470368528', NULL, 4, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1035, 'RSTGDI14R54E986F', 'caliandrolucrezia1978@gmail.com', '4791158baeb5fef6c0d611a309f1b9ed', 'Giada', 'Rosato', 'F', NULL, NULL, NULL, NULL, NULL, '3270030094', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1036, 'CMPFNC08B54E205O', 'campanellaf74@gmail.com', '118adb251c6bf9470d2f362312707c86', 'Francesca', 'Campanella', 'F', NULL, NULL, NULL, NULL, NULL, '3917622664', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1037, 'NSIMRP14P63L049T', 'carmela.galeone@libero.it', 'acd82c7ca6e512db0535e9c3ebca1ee6', 'Maria pia ', 'Nisi', 'F', NULL, NULL, NULL, NULL, NULL, '3248293377', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1038, 'LPPCML55R46C129T', 'carmelalippolis55@gmail.com', '25d55ad283aa400af464c76d713c07ad', 'Carmela', 'Lippolis', 'F', NULL, NULL, NULL, NULL, NULL, '3284109388', NULL, 9, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1039, 'MNDLRZ12B42L049A', 'chefvip@hotmail.it', '7543857aa5f83b936e019ce676bd294a', 'Lucrezia ', 'Amendola', 'F', NULL, NULL, NULL, NULL, NULL, '3472633398', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1040, 'MDCGAI19E62E986O', 'cinziaaprile80@gmail.com', 'b1b6d44125ccfc6035bd0c29e750583d', 'Gaia ', 'Medici ', 'F', NULL, NULL, NULL, NULL, NULL, '3401827844', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1041, 'LNTCRI65B23E205W', 'ciro.lenti@hotmail.com', 'dfff97663e4575acebb396011d3aaaf4', 'Ciro', 'Lenti ', 'M', NULL, NULL, NULL, NULL, NULL, '3281582168', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1042, 'LPUCRI72T26E205V', 'cirolupo1972@libero.it', '5f4ee049dc44297136ed46b3233d171a', 'Ciro', 'Lupo', 'M', NULL, NULL, NULL, NULL, NULL, '3246931823', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1043, 'LPUFRC14P64E205Q', 'cirolupo72@gmail.com', '3033e12e03aa8e71408410401b6580ef', 'Federica', 'Lupo', 'F', NULL, NULL, NULL, NULL, NULL, '3246931823', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1044, 'BRTCLD06H61E205O', 'claudiabritt18@gmail.com', '2003a1424aa1fb7164788574d286f88c', 'Claudia', 'Brittannico', 'F', NULL, NULL, NULL, NULL, NULL, '3926096049', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1045, 'TTNCLO08P61L049Y', 'cloeattanasi@gmail.com', '19ded32be0088842f1ed76499bedbd12', 'CLOE', 'ATTANASI ', 'F', NULL, NULL, NULL, NULL, NULL, '+393203243', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1046, 'TRNCMS64E50E986P', 'comasiaturnone0@gmail.com', '6f744703c4b99dd39d0eb984e27aedef', 'Comasia', 'Turnone', 'F', NULL, NULL, NULL, NULL, NULL, '3283619108', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1047, 'CRSCSM74D62E205R', 'crescenziomimma4791@gmail.com', '075a61b53b2686c240205cf2d202a51e', 'Cosima', 'Crescenzio', 'F', NULL, NULL, NULL, NULL, NULL, '3249891643', NULL, 9, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1048, 'CRSDNL83D51E205U', 'danielacre@alice.it', 'd51b3b9609a8e9f2d35c17d6cdeb65e5', 'Daniela', 'Crescenzio', 'F', NULL, NULL, NULL, NULL, NULL, '3888517783', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1049, 'CRVCRS13M55L049G', 'danielecarovigna@hotmail.it', '88da4b124ebc83d864aa25da387e1074', 'Christel ', 'Carovigna ', 'F', NULL, NULL, NULL, NULL, NULL, '3468781015', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1050, 'Rstmnl86l29f152u', 'emanuelerosato.com@gmail.com', '$2y$10$i9KWxgKZ9bxLrH3VjjoTauPPj.cKtbkKcGztC.R2WOFEPwsxureUq', 'BAntonio', 'aCasa', 'M', NULL, NULL, NULL, NULL, NULL, '1912179472', NULL, NULL, 1, 'admin', NULL, NULL, '2025-07-29 21:43:32', '2025-08-30 20:41:09', '2025-08-30 20:41:09', 0, NULL, NULL),
(1051, 'CVLMNL11B14E205C', 'family01rty@gmail.com', 'ee35cf4c591ad812d74e0cf6f74a3c08', 'Emmanuele ', 'Cavallo', 'M', NULL, NULL, NULL, NULL, NULL, '3290109867', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1052, 'PGLGAI14P55E205X', 'franciterranova1@gmail.com', 'ea799fedca074302aa181090ee32626a', 'Gaia', 'Pugliese ', 'F', NULL, NULL, NULL, NULL, NULL, '3398909663', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1053, 'SCPFNC57M04E205J', 'fscappati@gmail.com', '7396ce960f7289d780f25295755f24a4', 'Franco', 'Scappati', 'M', NULL, NULL, NULL, NULL, NULL, '3292041441', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1054, 'LGRMNL11E59E205P', 'gennycaputo71@gmail.com', '6d1f94153ea98a39741a5ebd51c604a1', 'Manuela ', 'ligorio ', 'F', NULL, NULL, NULL, NULL, NULL, '3494964899', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1055, 'MRNCLL17B56C136P', 'giggina79@yahoo.it', '1a6b97414564cb725e2310d7ec4dca7d', 'Camilla ', 'Murianni ', 'F', NULL, NULL, NULL, NULL, NULL, '3475284282', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1056, 'TRSFNC10R59B180K', 'Gipy1977@gmail.com', 'ce10ff6683021256c87fe35554279eb1', 'Francesca', 'Taurisano', 'M', NULL, NULL, NULL, NULL, NULL, '3476466586', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1057, 'NNCGTT09E48E205L', 'giudyanny2@gmail.com', '1bc6583ab1db680009137f8d4e9c7b56', 'Giuditta', 'Annicchiarico ', 'F', NULL, NULL, NULL, NULL, NULL, '3917492880', NULL, 4, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1058, 'LTNMRN12H48L049Z', 'giuseppe.latanza1080@gmail.com', '8ccc08a8eb24ade2b2ce1875147f3473', 'Miriana', 'Latanza', 'F', NULL, NULL, NULL, NULL, NULL, '3457925764', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1059, 'CRCNCH09R64E986D', 'giuseppecarucci86@libero.it', '84d2ee7aaf768e0d23e02072face33a8', 'Annachiara ', 'Carucci ', 'F', NULL, NULL, NULL, NULL, NULL, '3791872062', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1060, 'NSTGLI15D54D761S', 'grazia.miccoli@tim.it', '07a1278ed2575bb32663277f10ccec4e', 'Giulia', 'Anastasia', 'F', NULL, NULL, NULL, NULL, NULL, '3381989413', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1061, 'FMRGZN78L71E986D', 'grazianafumarola5@gmail.com', '76893523a8565e5efeb42566e35cb2c6', 'Graziana', 'Fumarola', 'F', NULL, NULL, NULL, NULL, NULL, '3479754814', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1062, 'GRNFRC14C50E205A', 'guarini.roberto@virgilio.it', '856db6b82524e39fc501bdedd4d70f06', 'Federica ', 'Guarini', 'F', NULL, NULL, NULL, NULL, NULL, '3471338256', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1063, 'DVNSRA13D64E205R', 'imma.dib85@gmail.com', 'dc90aeec38b5e1bdb7b001081beaed56', 'Sara', 'Di Venere', 'F', NULL, NULL, NULL, NULL, NULL, '3926876830', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1064, 'NCCSFO17S62D761Y', 'jasminedecarolis7@gmail.com', '9551c56c310c1529854eda17a1ee6304', 'Sofia', 'Nacci', 'F', NULL, NULL, NULL, NULL, NULL, '3892316058', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1065, 'MSLNTA13C46E205M', 'katiaarcadio@gmail.com', '5f73d3cc3f7dacca5b9cfbf42b30d77e', 'Anita', 'Masella', 'F', NULL, NULL, NULL, NULL, NULL, '3485755021', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1066, 'MNCGLI15L53Z404O', 'lesliegoldsmid@gmail.com', 'cb6ec12d07287b685330a795613732e1', 'Giulia', 'Manica', 'F', NULL, NULL, NULL, NULL, NULL, '3517799533', NULL, 4, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1067, 'DMCLTZ01M53E205Y', 'letiziada5@gmail.com', 'cdfaa6b24a3012cc0af2f437a8fc16b3', 'Letizia', 'D’Amicis ', 'F', NULL, NULL, NULL, NULL, NULL, '3271385620', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1068, 'NNCSAI12E55E205M', 'loryenastefani@gmail.com', 'e7f0d10c90e8f8f48ffc61e1f59cecb1', 'Asia', 'Annicchiarico', 'F', NULL, NULL, NULL, NULL, NULL, '3401987096', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1069, 'BNFLCU65R51D761R', 'lucia065@tiscali.it', 'b4863076d712555f655781ea95a1e5e1', 'Lucia', 'Bonfrate', 'F', NULL, NULL, NULL, NULL, NULL, '3292968319', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1070, 'RCCGGM07L44E017S', 'luciafinocchiaro1@gmail.com', 'c4715f313d712f6215c2730c32fcd661', 'Giorgia ', 'Ricca ', 'F', NULL, NULL, NULL, NULL, NULL, '3471924304', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1071, 'CSMLCL06S67Z604D', 'lucila.cosmani@gmail.com', '8c7f4d0d193af1ede563f334dd3833c2', 'Lucila', 'Cosmani ', 'F', NULL, NULL, NULL, NULL, NULL, '3922868202', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1072, 'DNCLNU06C48E205D', 'lunadanucci@gmail.com', 'ab2e62db38fa9cdd54d41697c3c8d166', 'Luna', 'Danucci', 'F', NULL, NULL, NULL, NULL, NULL, '3924007469', NULL, 4, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1073, 'LPUDZN07H48E205O', 'lupoluigi72@gmail.com', '6d91317463ff828c3f5e38d74f1c4f09', 'Domiziana', 'Lupo', 'F', NULL, NULL, NULL, NULL, NULL, '3921865824', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1074, 'LAOLCA11P60D761Q', 'marcello_lisa25@yahoo.it', 'b97062e8a846aa6197e27ae484b04bc3', 'Alice', 'Alò', 'F', NULL, NULL, NULL, NULL, NULL, '3206452148', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1075, 'MSLCST18T51L049H', 'marcorizzo2412@gmail.com', '9bbcd746c10f9bb0ed61f78baea9283d', 'Cristel', 'RIZZO MASELLA', 'F', NULL, NULL, NULL, NULL, NULL, '3453545491', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1076, 'STSMCH07R55E205T', 'mariachiarastasi2007@gmail.com', '4e97bb4d8617c08e8e8b1e098f6cca5e', 'Mariachiara', 'Stasi', 'F', NULL, NULL, NULL, NULL, NULL, '3472577628', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1077, 'LPUCSM11A07B180M', 'mariafontano80@gmail.com', 'd8d92de35944bbaa6bae4fe076ad8ad1', 'Cosimo', 'Lupo', 'M', NULL, NULL, NULL, NULL, NULL, '3288356087', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1078, 'BCCMRP56S43E882Q', 'mariapia.buccolieri@gmail.com', 'b3312281d1bf046998d9d82810d1897e', 'Mariapia', 'Buccolieri', 'F', NULL, NULL, NULL, NULL, NULL, '3474489352', NULL, 9, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1079, 'MRTGRL98M55L049U', 'MartinelliGabriella@outlook.it', '2e8486f9144d549006d3aa61d093cf92', 'Gabriella', 'Martinelli', 'F', NULL, NULL, NULL, NULL, NULL, '3203339182', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1080, 'BNCCLN16T44D761S', 'merollasandy@gmail.com', 'e1c565c5b1da2a3b81712427d06f5b34', 'Caroline', 'Bianco', 'F', NULL, NULL, NULL, NULL, NULL, '3883018679', NULL, 10, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1081, 'VNZMHL91D22E205O', 'mi.chy.91@hotmail.it', 'af8868d0da63a36b8f45a188e212096b', 'michele', 'venza', 'M', NULL, NULL, NULL, NULL, NULL, '3408730983', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1082, 'GSTMHL83E13E205S', 'mikelegg@hotmail.it', '95431e3bf1368501c834d7ee1ca5771e', 'Michele', 'Giustizieri', 'M', NULL, NULL, NULL, NULL, NULL, '3409295364', NULL, 12, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1083, 'BNGCSM60M03D761Q', 'mimmobuong@live.it', 'd5728a4d1bd40e7cb8400972e8d9536f', 'Cosimo', 'Buongiorno', 'M', NULL, NULL, NULL, NULL, NULL, '3393810935', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1084, 'MSCNNA65A46B808X', 'musanna65@hotmail.it', '3e2ee90d3ad7d0491b75e307662fac56', 'Anna', 'Musacchio', 'F', NULL, NULL, NULL, NULL, NULL, '3286004428', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1085, 'SCLGLI18S49L049O', 'n.veronica29@hotmail.it', '3d17fdad115cad7792ddbdb9964e5504', 'Giulia', 'Scialpi', 'F', NULL, NULL, NULL, NULL, NULL, '3396817300', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1086, 'VRSMNL16T70L049R', 'orsolamastro80@gmail.com', 'c5c1751d2ab5b7b38a3e6e8a868880cd', 'Emanuela ', 'Aversa', 'F', NULL, NULL, NULL, NULL, NULL, '3471455738', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1087, 'GNZLVC19R58D761L', 'palmieridesire47@gmail.com', '1a93da0ede5689fff409695bed7aa1cb', 'Ludovica', 'Ignazio', 'F', NULL, NULL, NULL, NULL, NULL, '3500430375', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1088, 'DMTDNS15D57E205A', 'paoladenisenicolas@gmail.com', '94b49702d2099cc1f9934cca7cf9ffdb', 'Denise', 'di Motoli', 'F', NULL, NULL, NULL, NULL, NULL, '3488138489', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1089, 'sprrfl11m46e205g', 'raffaellaspartano138@gmail.com', '582a7d4e64200b2d84fce2387f44d7d7', 'raffaella ', 'spartano', 'F', NULL, NULL, NULL, NULL, NULL, '3500106703', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1090, 'RBZMLY08R70B180D', 'ribezzoemily5@gmail.com', '6b9b53b799cb84a452d6d3707376e33c', 'Emily ', 'Ribezzo', 'F', NULL, NULL, NULL, NULL, NULL, '3713659665', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1091, 'MGGSVT70M13B180J', 'salvatore_maggiore@tiscali.it', '91870505c25d6ae24b6190760bb9b99e', 'Salvatore ', 'Maggiore', 'M', NULL, NULL, NULL, NULL, NULL, '3313701747', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1092, 'LTNSYR07B48E205Z', 'samyra.latanza@liceomoscati.edu.it', '8d9d101670a1d6f3584d6d7d63da7161', 'samyra', 'latanza', 'F', NULL, NULL, NULL, NULL, NULL, '3290325479', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1093, 'DNNPTT12B62E205T', 'santorocarmen@virgilio.it', 'ff67875f0b2f84d6165d106e7c5490f4', 'Deanna', 'Prettico ', 'F', NULL, NULL, NULL, NULL, NULL, '3384283833', NULL, 2, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1094, 'RGGPTR62A06E986A', 'scuolaguida90@gmail.com', '8d22ad30ccab2efde4dc13dce0b0abd3', 'Pietro', 'Ruggiero', 'M', NULL, NULL, NULL, NULL, NULL, '3714178974', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1095, 'VNCSLV74R49H501P', 'silviavinci@virgilio.it', '8240740b7374406a29b9e45366c2591d', 'Ginevra', 'Calzolaio', 'F', NULL, NULL, NULL, NULL, NULL, '3495649337', NULL, NULL, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1096, 'MNTSFO11P66E205H', 'sofiaurora12@gmail.com ', 'be2fec751a18879c7f3ec2b08f74a3c2', 'Sofia', 'Minetola', 'F', NULL, NULL, NULL, NULL, NULL, '3294746318', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1097, '2132132123113123', 'testutente@emanuelerosato.com', '$2y$10$sQojF1NEWnPb5i0LxYCbaO4z8g2FnhcZ/PnuqEt4oneH.Vn85l8QG', 'Emanuele', 'Rosato', 'M', NULL, NULL, NULL, NULL, NULL, '1232133212', NULL, 1, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-08-23 21:02:06', '2025-08-23 21:02:06', 0, NULL, NULL),
(1098, 'LBNSRA17C42A662U', 'tizianarochira71@gmail.com', '96abff1208ff899814db35279f01bd17', 'Sara', 'Albano ', 'F', NULL, NULL, NULL, NULL, NULL, '3477664654', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1099, 'MRNSMR14M55E205O', 'tizianavestita1991@gmail.com', 'bba8c51c466fc859e3263408ec44662c', 'Isia Maria', 'Marinelli', 'F', NULL, NULL, NULL, NULL, NULL, '3491066819', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1100, 'LZZLCA06T67A048G', 'valeria.abbamonte@gmail.com', '0d91a75bad7652838ccc07d892972828', 'Alice', 'Liuzzi', 'F', NULL, NULL, NULL, NULL, NULL, '3337432908', NULL, 5, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1101, 'SNTVRM14T64E205F', 'valeriaamely24@gmail.com', '5c3460197502cb4badb8a8a2e919003b', 'Amely', 'Santoro', 'F', NULL, NULL, NULL, NULL, NULL, '3272488013', NULL, 4, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL),
(1102, 'LNEMSS15L47D761U', 'vanessa96miccoli@gmail.com', '4743b3e30afc71c892108b4e482db50a', 'Melissa ', 'Leone', 'F', NULL, NULL, NULL, NULL, NULL, '3775962331', NULL, 3, 1, 'user', NULL, NULL, '2025-07-29 21:43:32', '2025-07-30 20:36:15', NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `subscription`
--

CREATE TABLE `subscription` (
  `cfSocio` varchar(16) NOT NULL,
  `endpoint` varchar(500) NOT NULL,
  `p256dh` varchar(500) NOT NULL,
  `auth` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `subscription`
--

INSERT INTO `subscription` (`cfSocio`, `endpoint`, `p256dh`, `auth`) VALUES
('ZZZZZZZZZZZZZZZZ', 'https://fcm.googleapis.com/fcm/send/eiDGmRkjw2E:APA91bHrvR40bv4X662uQlqvfdt2wg5m70ftkR6ZToI1wwmD0HLhMNg8H8I6BBJhYT16mzJPf0vRCC8gKDA2n9zcU4mcrpLFsxkE4TbIzkogRv_hMP7pv4_4KKSb-shrXVKfbq3ZObFe', 'BKP0phuTLeMU_xiagjRAY4GaqvelVsl9p22BdgLyXf5DCniLdXTMp1WXGXH8pbwdLEgHRiiwD4YUuhGIJDh6G5c', '4Rxgx6NFe4oaBRFu2jHxEQ');

-- --------------------------------------------------------

--
-- Struttura della tabella `system_metrics`
--

CREATE TABLE `system_metrics` (
  `id` int(11) NOT NULL,
  `metric_name` varchar(100) NOT NULL,
  `metric_value` decimal(15,4) DEFAULT NULL,
  `metric_unit` varchar(20) DEFAULT NULL,
  `metric_category` varchar(50) DEFAULT NULL,
  `collected_at` timestamp NULL DEFAULT current_timestamp(),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `system_notifications`
--

CREATE TABLE `system_notifications` (
  `id` int(11) NOT NULL,
  `type` enum('info','warning','error','success') DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `target_users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`target_users`)),
  `created_by` int(11) DEFAULT NULL,
  `read_by` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`read_by`)),
  `is_global` tinyint(1) DEFAULT 0,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `expires_at` timestamp NULL DEFAULT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `action_label` varchar(100) DEFAULT NULL,
  `is_dismissible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `uploads`
--

CREATE TABLE `uploads` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL COMMENT 'Generated unique filename',
  `original_name` varchar(255) NOT NULL COMMENT 'Original uploaded filename',
  `file_path` varchar(500) NOT NULL COMMENT 'Relative path from project root',
  `file_size` int(11) NOT NULL COMMENT 'File size in bytes',
  `mime_type` varchar(100) NOT NULL COMMENT 'MIME type of the file',
  `upload_type` enum('document','image','gallery') NOT NULL COMMENT 'Type of upload',
  `description` text DEFAULT NULL COMMENT 'Optional description',
  `thumbnail_path` varchar(500) DEFAULT NULL COMMENT 'Path to thumbnail (for images)',
  `uploaded_by` int(11) NOT NULL COMMENT 'User ID who uploaded the file',
  `uploaded_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Upload timestamp',
  `status` enum('active','deleted') DEFAULT 'active' COMMENT 'File status'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores information about uploaded files';

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `upload_stats`
-- (Vedi sotto per la vista effettiva)
--
CREATE TABLE `upload_stats` (
`upload_type` enum('document','image','gallery')
,`total_files` bigint(21)
,`total_size` decimal(32,0)
,`avg_size` decimal(14,4)
,`last_upload` timestamp
,`recent_uploads` bigint(21)
);

-- --------------------------------------------------------

--
-- Struttura della tabella `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `permission_value` tinyint(1) DEFAULT 1,
  `granted_by` int(11) DEFAULT NULL,
  `granted_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session` (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_last_activity` (`last_activity`),
  ADD KEY `idx_ip_address` (`ip_address`);

--
-- Indici per le tabelle `admin_backups`
--
ALTER TABLE `admin_backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_type` (`backup_type`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indici per le tabelle `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_type` (`log_type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_ip_address` (`ip_address`);

--
-- Indici per le tabelle `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_setting` (`category`,`setting_key`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indici per le tabelle `cartella`
--
ALTER TABLE `cartella`
  ADD PRIMARY KEY (`nome`);

--
-- Indici per le tabelle `corso`
--
ALTER TABLE `corso`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `idx_corso_nome` (`nome`),
  ADD KEY `idx_corso_attivo` (`attivo`);

--
-- Indici per le tabelle `documenti`
--
ALTER TABLE `documenti`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_tipo_file` (`tipo_file`),
  ADD KEY `idx_socio` (`socio_id`),
  ADD KEY `idx_corso` (`corso_id`),
  ADD KEY `idx_caricato_da` (`caricato_da`),
  ADD KEY `idx_visibilita` (`visibilita`),
  ADD KEY `idx_attivo` (`attivo`),
  ADD KEY `idx_creato` (`creato_il`);
ALTER TABLE `documenti` ADD FULLTEXT KEY `idx_search` (`nome`,`descrizione`,`nome_originale`);

--
-- Indici per le tabelle `documenti_categorie`
--
ALTER TABLE `documenti_categorie`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`),
  ADD KEY `idx_attiva` (`attiva`),
  ADD KEY `idx_ordine` (`ordine`);

--
-- Indici per le tabelle `documenti_download_log`
--
ALTER TABLE `documenti_download_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_documento` (`documento_id`),
  ADD KEY `idx_utente` (`utente_id`),
  ADD KEY `idx_timestamp` (`download_timestamp`);

--
-- Indici per le tabelle `evento`
--
ALTER TABLE `evento`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `orario`
--
ALTER TABLE `orario`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `socio`
--
ALTER TABLE `socio`
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `unique_cf` (`cf`);

--
-- Indici per le tabelle `sociocorso`
--
ALTER TABLE `sociocorso`
  ADD PRIMARY KEY (`cfSocio`,`idCorso`),
  ADD KEY `cfSocio` (`cfSocio`),
  ADD KEY `idCorso` (`idCorso`);

--
-- Indici per le tabelle `soci_new`
--
ALTER TABLE `soci_new`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cf` (`cf`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_cf` (`cf`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_nome_cognome` (`nome`,`cognome`),
  ADD KEY `idx_attivo` (`attivo`),
  ADD KEY `idx_corso` (`corso_principale`);

--
-- Indici per le tabelle `subscription`
--
ALTER TABLE `subscription`
  ADD PRIMARY KEY (`cfSocio`),
  ADD KEY `cfSocio` (`cfSocio`);

--
-- Indici per le tabelle `system_metrics`
--
ALTER TABLE `system_metrics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_metric_name` (`metric_name`),
  ADD KEY `idx_category` (`metric_category`),
  ADD KEY `idx_collected_at` (`collected_at`);

--
-- Indici per le tabelle `system_notifications`
--
ALTER TABLE `system_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_global` (`is_global`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indici per le tabelle `uploads`
--
ALTER TABLE `uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uploaded_by` (`uploaded_by`),
  ADD KEY `idx_upload_type` (`upload_type`),
  ADD KEY `idx_uploaded_at` (`uploaded_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_filename` (`filename`);

--
-- Indici per le tabelle `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_permission` (`user_id`,`permission_key`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_permission_key` (`permission_key`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `active_sessions`
--
ALTER TABLE `active_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `admin_backups`
--
ALTER TABLE `admin_backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT per la tabella `corso`
--
ALTER TABLE `corso`
  MODIFY `id` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT per la tabella `documenti`
--
ALTER TABLE `documenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `documenti_categorie`
--
ALTER TABLE `documenti_categorie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `documenti_download_log`
--
ALTER TABLE `documenti_download_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `evento`
--
ALTER TABLE `evento`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT per la tabella `orario`
--
ALTER TABLE `orario`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT per la tabella `soci_new`
--
ALTER TABLE `soci_new`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1180;

--
-- AUTO_INCREMENT per la tabella `system_metrics`
--
ALTER TABLE `system_metrics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `system_notifications`
--
ALTER TABLE `system_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `uploads`
--
ALTER TABLE `uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Struttura per vista `upload_stats`
--
DROP TABLE IF EXISTS `upload_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u361938811_dds07`@`localhost` SQL SECURITY DEFINER VIEW `upload_stats`  AS SELECT `uploads`.`upload_type` AS `upload_type`, count(0) AS `total_files`, sum(`uploads`.`file_size`) AS `total_size`, avg(`uploads`.`file_size`) AS `avg_size`, max(`uploads`.`uploaded_at`) AS `last_upload`, count(case when `uploads`.`uploaded_at` >= current_timestamp() - interval 7 day then 1 end) AS `recent_uploads` FROM `uploads` WHERE `uploads`.`status` = 'active' GROUP BY `uploads`.`upload_type` ;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `documenti_download_log`
--
ALTER TABLE `documenti_download_log`
  ADD CONSTRAINT `documenti_download_log_ibfk_1` FOREIGN KEY (`documento_id`) REFERENCES `documenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `sociocorso`
--
ALTER TABLE `sociocorso`
  ADD CONSTRAINT `corso` FOREIGN KEY (`idCorso`) REFERENCES `corso` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `socio` FOREIGN KEY (`cfSocio`) REFERENCES `socio` (`cf`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `soci_new`
--
ALTER TABLE `soci_new`
  ADD CONSTRAINT `soci_new_ibfk_1` FOREIGN KEY (`corso_principale`) REFERENCES `corso` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `subscription`
--
ALTER TABLE `subscription`
  ADD CONSTRAINT `cf` FOREIGN KEY (`cfSocio`) REFERENCES `socio` (`cf`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `uploads`
--
ALTER TABLE `uploads`
  ADD CONSTRAINT `fk_uploads_user` FOREIGN KEY (`uploaded_by`) REFERENCES `soci_new` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
