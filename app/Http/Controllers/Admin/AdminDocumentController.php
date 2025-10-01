<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminBaseController;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminDocumentController extends AdminBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Document::with(['uploadedBy', 'approvedBy']);

        // Filtri
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->filled('search')) {
            $search = \App\Helpers\QueryHelper::sanitizeLikeInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('original_filename', 'like', "%{$search}%")
                  ->orWhereHas('uploadedBy', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Ordinamento
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $documents = $query->orderBy($sortBy, $sortDirection)->paginate(15);

        // Statistiche
        $statistics = [
            'total' => Document::count(),
            'pending' => Document::pending()->count(),
            'approved' => Document::approved()->count(),
            'rejected' => Document::rejected()->count(),
            'expired' => Document::expired()->count(),
            'total_size' => $this->formatBytes(Document::sum('file_size')),
            'categories' => Document::select('category')
                               ->selectRaw('count(*) as count')
                               ->groupBy('category')
                               ->pluck('count', 'category')
                               ->toArray()
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.documents.partials.documents-table', compact('documents'))->render(),
                'pagination' => $documents->links()->render(),
                'statistics' => $statistics
            ]);
        }

        return view('admin.documents.index', compact('documents', 'statistics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get all students from the current school for dropdown
        $students = auth()->user()->school->users()
            ->where('role', 'user')
            ->where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email', 'codice_fiscale']);

        return view('admin.documents.create', compact('students'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        // FormRequest già valida name, category, file - non serve validazione aggiuntiva

        try {
            $file = $request->file('file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();

            // Secure filename generation (consistent with StudentDocumentController)
            $safeName = Str::slug($request->name) . '-' . time() . '-' . Str::random(8);
            $filename = $safeName . '.' . $extension;

            // Admin documents go to school folder
            $schoolId = auth()->user()->school_id;
            $filePath = $file->storeAs("documents/{$schoolId}/admin", $filename, 'private');

            $document = Document::create([
                'school_id' => $schoolId,
                'user_id' => $request->user_id ?: auth()->id(), // Admin or selected student
                'name' => $request->name,
                'file_path' => $filePath,
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'category' => $request->category,
                'status' => 'approved', // Admin uploads are auto-approved
                'uploaded_at' => now(),
            ]);

            // Se non richiede approvazione, approvalo automaticamente
            if (!$request->boolean('requires_approval', true)) {
                $document->approve();
            }

            Log::info('Document uploaded successfully', [
                'document_id' => $document->id,
                'title' => $document->title,
                'uploaded_by' => auth()->user()->name,
                'school_id' => auth()->user()->school_id
            ]);

            return redirect()->route('admin.documents.index')
                           ->with('success', 'Documento caricato con successo.');

        } catch (\Exception $e) {
            Log::error('Error uploading document', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'school_id' => auth()->user()->school_id
            ]);

            return back()->withErrors(['file' => 'Errore durante il caricamento del documento.'])
                         ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        // Verifica che il documento appartenga alla scuola dell'admin
        if ($document->school_id !== auth()->user()->school_id) {
            abort(404);
        }

        return view('admin.documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        // Verifica che il documento appartenga alla scuola dell'admin
        if ($document->school_id !== auth()->user()->school_id) {
            abort(404);
        }

        return view('admin.documents.edit', compact('document'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocumentRequest $request, Document $document)
    {
        // Verifica che il documento appartenga alla scuola dell'admin
        if ($document->school_id !== auth()->user()->school_id) {
            abort(404);
        }

        // SECURITY: Validation with magic bytes check done in UpdateDocumentRequest

        try {
            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'is_public' => $request->boolean('is_public'),
                'requires_approval' => $request->boolean('requires_approval'),
                'expires_at' => $request->expires_at ? now()->parse($request->expires_at) : null,
            ];

            // Se viene caricato un nuovo file
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $storedName = Str::uuid() . '.' . $extension;

                // Elimina il file precedente se esiste
                if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
                    Storage::disk('private')->delete($document->file_path);
                }

                // Salva il nuovo file
                $filePath = $file->storeAs('documents', $storedName, 'private');

                $data = array_merge($data, [
                    'original_filename' => $originalName,
                    'stored_filename' => $storedName,
                    'file_path' => $filePath,
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);

                // Se il documento era approvato e ora richiede approvazione, rimettilo in pending
                if ($document->status === 'approved' && $request->boolean('requires_approval')) {
                    $data['status'] = 'pending';
                    $data['approved_by'] = null;
                    $data['approved_at'] = null;
                }
            }

            $document->update($data);

            Log::info('Document updated successfully', [
                'document_id' => $document->id,
                'title' => $document->title,
                'updated_by' => auth()->user()->name,
                'school_id' => auth()->user()->school_id
            ]);

            return redirect()->route('admin.documents.show', $document)
                           ->with('success', 'Documento aggiornato con successo.');

        } catch (\Exception $e) {
            Log::error('Error updating document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'school_id' => auth()->user()->school_id
            ]);

            return back()->withErrors(['error' => 'Errore durante l\'aggiornamento del documento.'])
                         ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        // Verifica che il documento appartenga alla scuola dell'admin
        if ($document->school_id !== auth()->user()->school_id) {
            abort(404);
        }

        try {
            // Elimina il file fisico se esiste
            if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
                Storage::disk('private')->delete($document->file_path);
            }

            $title = $document->title;
            $document->delete();

            Log::info('Document deleted successfully', [
                'document_title' => $title,
                'deleted_by' => auth()->user()->name,
                'school_id' => auth()->user()->school_id
            ]);

            return redirect()->route('admin.documents.index')
                           ->with('success', 'Documento eliminato con successo.');

        } catch (\Exception $e) {
            Log::error('Error deleting document', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'school_id' => auth()->user()->school_id
            ]);

            return back()->withErrors(['error' => 'Errore durante l\'eliminazione del documento.']);
        }
    }

    /**
     * Download document file
     */
    public function download(Document $document)
    {
        // Verifica che il documento appartenga alla scuola dell'admin
        if ($document->school_id !== auth()->user()->school_id) {
            abort(404);
        }

        if (!$document->file_path || !Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'File non trovato');
        }

        return Storage::disk('private')->download($document->file_path, $document->original_filename);
    }

    /**
     * Approve document
     */
    public function approve(Document $document)
    {
        // Verifica che il documento appartenga alla scuola dell'admin
        if ($document->school_id !== auth()->user()->school_id) {
            abort(404);
        }

        if ($document->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo i documenti in attesa possono essere approvati.']);
        }

        $document->approve();

        Log::info('Document approved', [
            'document_id' => $document->id,
            'title' => $document->title,
            'approved_by' => auth()->user()->name,
            'school_id' => auth()->user()->school_id
        ]);

        return back()->with('success', 'Documento approvato con successo.');
    }

    /**
     * Reject document
     */
    public function reject(Request $request, Document $document)
    {
        // Verifica che il documento appartenga alla scuola dell'admin
        if ($document->school_id !== auth()->user()->school_id) {
            abort(404);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        if ($document->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo i documenti in attesa possono essere rifiutati.']);
        }

        $document->reject($request->rejection_reason);

        Log::info('Document rejected', [
            'document_id' => $document->id,
            'title' => $document->title,
            'rejection_reason' => $request->rejection_reason,
            'rejected_by' => auth()->user()->name,
            'school_id' => auth()->user()->school_id
        ]);

        return back()->with('success', 'Documento rifiutato.');
    }

    /**
     * Bulk actions on multiple documents
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'documents' => 'required|array|min:1',
            'documents.*' => 'exists:documents,id',
            'rejection_reason' => 'required_if:action,reject|string|max:1000'
        ]);

        $documents = Document::whereIn('id', $request->documents)
                           ->where('school_id', auth()->user()->school_id)
                           ->get();

        if ($documents->isEmpty()) {
            return back()->withErrors(['error' => 'Nessun documento selezionato valido.']);
        }

        $count = 0;

        foreach ($documents as $document) {
            try {
                switch ($request->action) {
                    case 'approve':
                        if ($document->status === 'pending') {
                            $document->approve();
                            $count++;
                        }
                        break;

                    case 'reject':
                        if ($document->status === 'pending') {
                            $document->reject($request->rejection_reason);
                            $count++;
                        }
                        break;

                    case 'delete':
                        if ($document->file_path && Storage::disk('private')->exists($document->file_path)) {
                            Storage::disk('private')->delete($document->file_path);
                        }
                        $document->delete();
                        $count++;
                        break;
                }
            } catch (\Exception $e) {
                Log::error('Error in bulk action for document', [
                    'document_id' => $document->id,
                    'action' => $request->action,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $message = match($request->action) {
            'approve' => "Approvati {$count} documenti.",
            'reject' => "Rifiutati {$count} documenti.",
            'delete' => "Eliminati {$count} documenti.",
        };

        return back()->with('success', $message);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        if ($bytes == 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log(1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}