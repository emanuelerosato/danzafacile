<?php

namespace Tests\Feature\Admin;

use App\Models\School;
use App\Models\User;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Test di riproduzione bug upload documenti
 *
 * BUG REPORT:
 * URL: https://www.danzafacile.it/admin/documents/create
 *
 * SINTOMI:
 * - Upload NON da errore visibile
 * - Messaggio di successo viene mostrato
 * - Documento NON appare in lista
 * - Documento NON viene salvato nel database
 *
 * ROOT CAUSES IDENTIFICATE:
 *
 * 1. ENUM CATEGORY MISMATCH
 *    - Migration permette solo: 'medical', 'photo', 'agreement'
 *    - Model definisce: 'general', 'medical', 'contract', 'identification', 'other'
 *    - Form permette di selezionare categorie non supportate dal database
 *    - SQL error silenzioso quando categoria non valida
 *
 * 2. MISSING DATABASE FIELDS
 *    - Controller usa: 'title', 'description', 'original_filename'
 *    - Database ha solo: 'name' (non 'title')
 *    - Nessun campo 'description' o 'original_filename' nella migration
 *
 * 3. SILENT EXCEPTION HANDLING
 *    - try-catch nel controller cattura l'errore
 *    - Redirect con successo avviene comunque
 *    - Utente non vede errore reale
 */
class AdminDocumentUploadBugReproductionTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup scuola e utenti
        $this->school = School::factory()->create([
            'name' => 'Test School for Document Bug',
            'active' => true,
        ]);

        $this->admin = User::factory()->admin()->create([
            'school_id' => $this->school->id,
            'first_name' => 'Admin',
            'last_name' => 'Tester',
            'email' => 'admin@documentbug.test',
        ]);

        $this->student = User::factory()->student()->create([
            'school_id' => $this->school->id,
            'first_name' => 'Student',
            'last_name' => 'Test',
            'email' => 'student@documentbug.test',
        ]);

        // Setup storage fake
        Storage::fake('private');
    }

    /**
     * TEST 1: Riproduzione bug con categoria 'general' (non supportata dal DB)
     *
     * EXPECTED: SQL error perché 'general' non è in ENUM('medical', 'photo', 'agreement')
     * ACTUAL: Upload fallisce silenziosamente, utente vede messaggio di successo
     */
    public function test_upload_document_with_unsupported_category_general_fails_silently(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('test-document.pdf', 500, 'application/pdf');

        $documentData = [
            'name' => 'Test Document General Category',
            'category' => 'general', // ❌ Non supportata dal database!
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        $documentsCountBefore = Document::count();

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $documentsCountAfter = Document::count();

        // BUG: Il documento NON viene salvato ma il redirect avviene con successo
        $this->assertEquals($documentsCountBefore, $documentsCountAfter,
            'EXPECTED: Document count should NOT increase because category is invalid');

        // BUG: Utente vede messaggio di successo anche se upload fallito
        $response->assertSessionHas('success', 'Documento caricato con successo.');

        // BUG: File NON dovrebbe esistere nello storage ma viene comunque salvato prima del fallimento
        // Il file potrebbe esistere temporaneamente prima del rollback
    }

    /**
     * TEST 2: Riproduzione bug con categoria 'contract' (non supportata)
     */
    public function test_upload_document_with_unsupported_category_contract_fails_silently(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('contratto.pdf', 500, 'application/pdf');

        $documentData = [
            'name' => 'Contratto Studente',
            'category' => 'contract', // ❌ Non supportata (dovrebbe essere 'agreement'?)
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $this->assertDatabaseMissing('documents', [
            'name' => 'Contratto Studente',
            'category' => 'contract',
        ]);

        // BUG: Messaggio di successo anche se documento non salvato
        $response->assertSessionHas('success');
    }

    /**
     * TEST 3: Riproduzione bug con categoria 'identification' (non supportata)
     */
    public function test_upload_document_with_unsupported_category_identification_fails_silently(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('carta-identita.jpg', 500, 'image/jpeg');

        $documentData = [
            'name' => 'Carta Identità',
            'category' => 'identification', // ❌ Non supportata
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $this->assertDatabaseMissing('documents', [
            'name' => 'Carta Identità',
            'category' => 'identification',
        ]);
    }

    /**
     * TEST 4: Upload con categoria SUPPORTATA 'medical' dovrebbe funzionare
     *
     * Questo test DOVREBBE passare se solo il bug delle categorie è il problema
     */
    public function test_upload_document_with_supported_category_medical_should_work(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('certificato-medico.pdf', 500, 'application/pdf');

        $documentData = [
            'name' => 'Certificato Medico 2026',
            'category' => 'medical', // ✅ Supportata dal database
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert - Questo dovrebbe passare
        $response->assertRedirect(route('admin.documents.index'));
        $response->assertSessionHas('success', 'Documento caricato con successo.');

        $this->assertDatabaseHas('documents', [
            'name' => 'Certificato Medico 2026',
            'category' => 'medical',
            'school_id' => $this->school->id,
            'user_id' => $this->student->id,
            'status' => 'approved', // Admin uploads sono auto-approvati
        ]);
    }

    /**
     * TEST 5: Upload con categoria 'photo' (supportata)
     */
    public function test_upload_document_with_supported_category_photo_should_work(): void
    {
        // Arrange
        $file = UploadedFile::fake()->image('foto-studente.jpg');

        $documentData = [
            'name' => 'Foto Studente',
            'category' => 'photo', // ✅ Supportata
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $response->assertRedirect(route('admin.documents.index'));

        $this->assertDatabaseHas('documents', [
            'name' => 'Foto Studente',
            'category' => 'photo',
        ]);
    }

    /**
     * TEST 6: Upload con categoria 'agreement' (supportata)
     */
    public function test_upload_document_with_supported_category_agreement_should_work(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('accordo.pdf', 500, 'application/pdf');

        $documentData = [
            'name' => 'Accordo Privacy',
            'category' => 'agreement', // ✅ Supportata
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $response->assertRedirect(route('admin.documents.index'));

        $this->assertDatabaseHas('documents', [
            'name' => 'Accordo Privacy',
            'category' => 'agreement',
        ]);
    }

    /**
     * TEST 7: Verifica che il file venga salvato nello storage path corretto
     */
    public function test_document_file_is_stored_in_correct_path(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.pdf', 500, 'application/pdf');

        $documentData = [
            'name' => 'Test Storage Path',
            'category' => 'medical',
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        if ($response->isRedirect() && session('success')) {
            $document = Document::where('name', 'Test Storage Path')->first();

            if ($document) {
                // Verifica che il file esista nello storage
                Storage::disk('private')->assertExists($document->file_path);

                // Verifica che il path segua il pattern: documents/{school_id}/admin/{filename}
                $this->assertStringContainsString(
                    "documents/{$this->school->id}/admin/",
                    $document->file_path,
                    'File should be stored in school-specific admin folder'
                );
            } else {
                $this->fail('Document was not saved to database despite success message');
            }
        }
    }

    /**
     * TEST 8: Multi-tenant isolation - Admin NON deve poter associare documento a studente di altra scuola
     */
    public function test_admin_cannot_upload_document_for_student_from_different_school(): void
    {
        // Arrange
        $otherSchool = School::factory()->create(['name' => 'Other School']);
        $otherStudent = User::factory()->student()->create([
            'school_id' => $otherSchool->id,
            'email' => 'student@otherschool.test',
        ]);

        $file = UploadedFile::fake()->create('test.pdf', 500, 'application/pdf');

        $documentData = [
            'name' => 'Unauthorized Document',
            'category' => 'medical',
            'user_id' => $otherStudent->id, // ❌ Studente di altra scuola
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert - Dovrebbe fallire la validazione
        $response->assertSessionHasErrors('user_id');

        $this->assertDatabaseMissing('documents', [
            'name' => 'Unauthorized Document',
            'user_id' => $otherStudent->id,
        ]);
    }

    /**
     * TEST 9: Upload senza user_id (documento generale della scuola)
     */
    public function test_upload_general_school_document_without_student_association(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('regolamento-scuola.pdf', 500, 'application/pdf');

        $documentData = [
            'name' => 'Regolamento Interno',
            'category' => 'agreement',
            'user_id' => '', // Documento generale, non associato a studente
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $response->assertRedirect(route('admin.documents.index'));

        $document = Document::where('name', 'Regolamento Interno')->first();

        if ($document) {
            // user_id dovrebbe essere l'admin che ha caricato
            $this->assertEquals($this->admin->id, $document->user_id);
        } else {
            $this->fail('Document should be created even without explicit student association');
        }
    }

    /**
     * TEST 10: Verifica dimensione file oltre il limite (10MB)
     */
    public function test_upload_document_exceeding_size_limit_should_fail(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('huge-file.pdf', 11000, 'application/pdf'); // 11MB

        $documentData = [
            'name' => 'File Troppo Grande',
            'category' => 'medical',
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $response->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('documents', [
            'name' => 'File Troppo Grande',
        ]);
    }

    /**
     * TEST 11: Verifica tipo file non permesso
     */
    public function test_upload_document_with_invalid_file_type_should_fail(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('malicious.exe', 500, 'application/x-msdownload');

        $documentData = [
            'name' => 'Malicious File',
            'category' => 'medical',
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $response->assertSessionHasErrors('file');

        $this->assertDatabaseMissing('documents', [
            'name' => 'Malicious File',
        ]);
    }

    /**
     * TEST 12: Verifica che documenti caricati dall'admin siano auto-approvati
     */
    public function test_admin_uploaded_documents_are_auto_approved(): void
    {
        // Arrange
        $file = UploadedFile::fake()->create('auto-approved.pdf', 500, 'application/pdf');

        $documentData = [
            'name' => 'Auto Approved Document',
            'category' => 'medical',
            'user_id' => $this->student->id,
            'file' => $file,
        ];

        // Act
        $response = $this->actingAs($this->admin)->post(route('admin.documents.store'), $documentData);

        // Assert
        $document = Document::where('name', 'Auto Approved Document')->first();

        if ($document) {
            $this->assertEquals('approved', $document->status,
                'Admin uploads should be auto-approved');
            $this->assertNotNull($document->uploaded_at,
                'uploaded_at should be set');
        }
    }

    /**
     * TEST 13: Lista documenti NON mostra documenti di altre scuole
     */
    public function test_document_index_shows_only_school_documents(): void
    {
        // Arrange
        $otherSchool = School::factory()->create();
        $otherAdmin = User::factory()->admin()->create(['school_id' => $otherSchool->id]);

        // Crea documento per scuola corrente
        $myDocument = Document::factory()->create([
            'school_id' => $this->school->id,
            'user_id' => $this->admin->id,
            'name' => 'My School Document',
            'category' => 'medical',
        ]);

        // Crea documento per altra scuola
        $otherDocument = Document::factory()->create([
            'school_id' => $otherSchool->id,
            'user_id' => $otherAdmin->id,
            'name' => 'Other School Document',
            'category' => 'medical',
        ]);

        // Act
        $response = $this->actingAs($this->admin)->get(route('admin.documents.index'));

        // Assert
        $response->assertSee('My School Document');
        $response->assertDontSee('Other School Document');
    }
}
