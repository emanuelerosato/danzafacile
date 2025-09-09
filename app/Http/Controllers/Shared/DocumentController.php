<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents
     * Access varies by user role
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Document::with('user');

        // Role-based filtering
        if ($user->isSuperAdmin()) {
            // Super admin can see all documents
        } elseif ($user->isAdmin()) {
            // Admin can see documents from their school only
            $query->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        } else {
            // Students can see only their own documents
            $query->where('user_id', $user->id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($subq) use ($search) {
                      $subq->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by user (for admin/super-admin)
        if ($request->filled('user_id') && ($user->isAdmin() || $user->isSuperAdmin())) {
            $query->where('user_id', $request->get('user_id'));
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get users for filter (role-based)
        $users = collect();
        if ($user->isSuperAdmin()) {
            $users = User::orderBy('name')->get();
        } elseif ($user->isAdmin()) {
            $users = User::where('school_id', $user->school_id)->orderBy('name')->get();
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('shared.documents.partials.table', compact('documents'))->render(),
                'pagination' => $documents->links()->render()
            ]);
        }

        return view('shared.documents.index', compact('documents', 'users'));
    }

    /**
     * Show the form for creating a new document
     */
    public function create()
    {
        $user = auth()->user();
        
        // For admin/super-admin, get list of users they can upload documents for
        $users = collect();
        if ($user->isSuperAdmin()) {
            $users = User::where('role', '!=', User::ROLE_SUPER_ADMIN)->orderBy('name')->get();
        } elseif ($user->isAdmin()) {
            $users = User::where('school_id', $user->school_id)
                        ->where('role', '!=', User::ROLE_SUPER_ADMIN)
                        ->orderBy('name')
                        ->get();
        }

        return view('shared.documents.create', compact('users'));
    }

    /**
     * Store a newly created document
     */
    public function store(StoreDocumentRequest $request)
    {
        $user = auth()->user();
        $data = $request->validated();

        // For students, enforce their own user_id
        if ($user->isStudent()) {
            $data['user_id'] = $user->id;
        }

        // For admin, ensure user belongs to their school
        if ($user->isAdmin()) {
            $targetUser = User::findOrFail($data['user_id']);
            if ($targetUser->school_id !== $user->school_id) {
                abort(403, 'Non puoi caricare documenti per utenti di altre scuole.');
            }
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('documents', $filename, 'private');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }

        $data['status'] = 'pending';
        $data['uploaded_by'] = $user->id;

        $document = Document::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Documento caricato con successo.',
                'document' => $document->load('user')
            ]);
        }

        return redirect()->route('documents.index')
                        ->with('success', 'Documento caricato con successo.');
    }

    /**
     * Display the specified document
     */
    public function show(Document $document)
    {
        $this->authorizeDocument($document);

        $document->load('user');

        return view('shared.documents.show', compact('document'));
    }

    /**
     * Show the form for editing the specified document
     */
    public function edit(Document $document)
    {
        $this->authorizeDocument($document);

        $user = auth()->user();
        
        // Get users for admin/super-admin
        $users = collect();
        if ($user->isSuperAdmin()) {
            $users = User::where('role', '!=', User::ROLE_SUPER_ADMIN)->orderBy('name')->get();
        } elseif ($user->isAdmin()) {
            $users = User::where('school_id', $user->school_id)
                        ->where('role', '!=', User::ROLE_SUPER_ADMIN)
                        ->orderBy('name')
                        ->get();
        }

        return view('shared.documents.edit', compact('document', 'users'));
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, Document $document)
    {
        $this->authorizeDocument($document);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:medical_certificate,identity_document,insurance,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'expiry_date' => 'nullable|date|after:today',
            'is_required' => 'boolean',
        ]);

        $data = $request->except(['file']);

        // Authorization checks for user_id change
        $user = auth()->user();
        if ($user->isAdmin() && $data['user_id'] != $document->user_id) {
            $targetUser = User::findOrFail($data['user_id']);
            if ($targetUser->school_id !== $user->school_id) {
                abort(403, 'Non autorizzato');
            }
        }

        // Handle file replacement
        if ($request->hasFile('file')) {
            // Delete old file
            if ($document->file_path) {
                Storage::disk('private')->delete($document->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('documents', $filename, 'private');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
            $data['status'] = 'pending'; // Reset status when file changes
        }

        $document->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Documento aggiornato con successo.',
                'document' => $document->fresh()->load('user')
            ]);
        }

        return redirect()->route('documents.show', $document)
                        ->with('success', 'Documento aggiornato con successo.');
    }

    /**
     * Remove the specified document
     */
    public function destroy(Document $document)
    {
        $this->authorizeDocument($document);

        // Delete file from storage
        if ($document->file_path) {
            Storage::disk('private')->delete($document->file_path);
        }

        $document->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Documento eliminato con successo.'
            ]);
        }

        return redirect()->route('documents.index')
                        ->with('success', 'Documento eliminato con successo.');
    }

    /**
     * Download document file
     */
    public function download(Document $document)
    {
        $this->authorizeDocument($document);

        if (!$document->file_path || !Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'File non trovato.');
        }

        return Storage::disk('private')->download($document->file_path, $document->file_name);
    }

    /**
     * Approve document (admin/super-admin only)
     */
    public function approve(Document $document)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Non autorizzato');
        }

        // Admin can approve only documents from their school
        if ($user->isAdmin() && $document->user->school_id !== $user->school_id) {
            abort(403, 'Non autorizzato');
        }

        $document->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now()
        ]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Documento approvato con successo.',
                'document' => $document->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Documento approvato con successo.');
    }

    /**
     * Reject document (admin/super-admin only)
     */
    public function reject(Request $request, Document $document)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            abort(403, 'Non autorizzato');
        }

        // Admin can reject only documents from their school
        if ($user->isAdmin() && $document->user->school_id !== $user->school_id) {
            abort(403, 'Non autorizzato');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $document->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => $user->id,
            'reviewed_at' => now()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Documento rifiutato.',
                'document' => $document->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Documento rifiutato.');
    }

    /**
     * Bulk actions for documents
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
            'rejection_reason' => 'required_if:action,reject|string|max:500'
        ]);

        $user = auth()->user();
        $documentIds = $request->get('document_ids');
        $action = $request->get('action');

        // Get documents with authorization check
        $query = Document::whereIn('id', $documentIds);

        if ($user->isAdmin()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        } elseif ($user->isStudent()) {
            $query->where('user_id', $user->id);
        }

        $documents = $query->get();

        foreach ($documents as $document) {
            switch ($action) {
                case 'approve':
                    if ($user->isAdmin() || $user->isSuperAdmin()) {
                        $document->update([
                            'status' => 'approved',
                            'reviewed_by' => $user->id,
                            'reviewed_at' => now()
                        ]);
                    }
                    break;

                case 'reject':
                    if ($user->isAdmin() || $user->isSuperAdmin()) {
                        $document->update([
                            'status' => 'rejected',
                            'rejection_reason' => $request->rejection_reason,
                            'reviewed_by' => $user->id,
                            'reviewed_at' => now()
                        ]);
                    }
                    break;

                case 'delete':
                    if ($document->file_path) {
                        Storage::disk('private')->delete($document->file_path);
                    }
                    $document->delete();
                    break;
            }
        }

        $message = match($action) {
            'approve' => 'Documenti approvati con successo.',
            'reject' => 'Documenti rifiutati.',
            'delete' => 'Documenti eliminati con successo.',
        };

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Check if user can access/modify document
     */
    private function authorizeDocument(Document $document)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return; // Super admin can access all
        }

        if ($user->isAdmin()) {
            if ($document->user->school_id !== $user->school_id) {
                abort(403, 'Non autorizzato');
            }
            return;
        }

        if ($user->isStudent()) {
            if ($document->user_id !== $user->id) {
                abort(403, 'Non autorizzato');
            }
            return;
        }

        abort(403, 'Non autorizzato');
    }
}