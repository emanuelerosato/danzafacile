<?php

namespace App\Http\Controllers\API;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;

class DocumentController extends BaseApiController
{
    /**
     * Get documents list
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Document::with(['user', 'uploadedBy']);

        // Role-based filtering
        if ($user->isAdmin()) {
            // Admin sees all documents of their school
            $query->where('school_id', $user->school_id);
        } elseif ($user->isStudent()) {
            // Students see only their own documents
            $query->where('user_id', $user->id);
        } else {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id') && $user->isAdmin()) {
            $query->where('user_id', $request->user_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = \App\Helpers\QueryHelper::sanitizeLikeInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $documents = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'documents' => $documents->items(),
            'pagination' => [
                'current_page' => $documents->currentPage(),
                'total_pages' => $documents->lastPage(),
                'per_page' => $documents->perPage(),
                'total' => $documents->total(),
            ]
        ]);
    }

    /**
     * Get document statistics
     */
    public function statistics(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono visualizzare le statistiche', 403);
        }

        $query = Document::where('school_id', $user->school_id);

        $stats = [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'rejected' => (clone $query)->where('status', 'rejected')->count(),
            'by_type' => (clone $query)->select('type', \DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];

        return $this->successResponse($stats);
    }

    /**
     * Upload new document
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Use existing Form Request for validation if available
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:identity_card,tax_code,medical_certificate,privacy_consent,photo_consent,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB
            'user_id' => $user->isAdmin() ? 'nullable|exists:users,id' : 'prohibited',
        ]);

        // Handle file upload
        if (!$request->hasFile('file')) {
            return $this->errorResponse('File non presente', 422);
        }

        $file = $request->file('file');

        // Validate file with FileUploadHelper
        $validation = \App\Helpers\FileUploadHelper::validateFile($file, ['pdf', 'jpg', 'jpeg', 'png'], 10240);

        if (!$validation['valid']) {
            return $this->errorResponse($validation['error'], 422);
        }

        // Store file
        $path = $file->store('documents', 'public');

        // Determine user_id
        $targetUserId = $user->isAdmin() && $request->filled('user_id')
            ? $request->user_id
            : $user->id;

        // Create document
        $document = Document::create([
            'user_id' => $targetUserId,
            'school_id' => $user->school_id,
            'uploaded_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'pending',
        ]);

        $document->load(['user', 'uploadedBy']);

        return $this->successResponse([
            'document' => $document,
            'message' => 'Documento caricato con successo'
        ], 201);
    }

    /**
     * Show document details
     */
    public function show($id)
    {
        $user = Auth::user();

        $document = Document::with(['user', 'uploadedBy'])->find($id);

        if (!$document) {
            return $this->errorResponse('Documento non trovato', 404);
        }

        // Check authorization
        if ($user->isStudent() && $document->user_id !== $user->id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isAdmin() && $document->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        return $this->successResponse(['document' => $document]);
    }

    /**
     * Update document metadata (admin only)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono aggiornare i documenti', 403);
        }

        $document = Document::find($id);

        if (!$document) {
            return $this->errorResponse('Documento non trovato', 404);
        }

        if ($document->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|in:identity_card,tax_code,medical_certificate,privacy_consent,photo_consent,other',
            'status' => 'nullable|in:pending,approved,rejected',
        ]);

        $document->update(array_filter($validated));

        return $this->successResponse([
            'document' => $document->fresh(['user', 'uploadedBy']),
            'message' => 'Documento aggiornato con successo'
        ]);
    }

    /**
     * Delete document
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $document = Document::find($id);

        if (!$document) {
            return $this->errorResponse('Documento non trovato', 404);
        }

        // Authorization
        if ($user->isStudent() && $document->user_id !== $user->id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isAdmin() && $document->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return $this->successResponse([
            'message' => 'Documento eliminato con successo'
        ]);
    }

    /**
     * Download document
     */
    public function download($id)
    {
        $user = Auth::user();

        $document = Document::find($id);

        if (!$document) {
            return $this->errorResponse('Documento non trovato', 404);
        }

        // Authorization
        if ($user->isStudent() && $document->user_id !== $user->id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isAdmin() && $document->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $filePath = storage_path('app/public/' . $document->file_path);

        if (!file_exists($filePath)) {
            return $this->errorResponse('File non trovato', 404);
        }

        return response()->download($filePath, $document->file_name);
    }

    /**
     * Approve document (admin only)
     */
    public function approve($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono approvare i documenti', 403);
        }

        $document = Document::find($id);

        if (!$document) {
            return $this->errorResponse('Documento non trovato', 404);
        }

        if ($document->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $document->update(['status' => 'approved']);

        return $this->successResponse([
            'document' => $document,
            'message' => 'Documento approvato con successo'
        ]);
    }

    /**
     * Reject document (admin only)
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono rifiutare i documenti', 403);
        }

        $document = Document::find($id);

        if (!$document) {
            return $this->errorResponse('Documento non trovato', 404);
        }

        if ($document->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $document->update([
            'status' => 'rejected',
            'description' => $validated['reason'] ?? 'Documento rifiutato'
        ]);

        return $this->successResponse([
            'document' => $document,
            'message' => 'Documento rifiutato'
        ]);
    }

    /**
     * Bulk actions (admin only)
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono eseguire azioni multiple', 403);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,delete',
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        $documents = Document::whereIn('id', $validated['document_ids'])
            ->where('school_id', $user->school_id)
            ->get();

        $count = 0;

        foreach ($documents as $document) {
            switch ($validated['action']) {
                case 'approve':
                    $document->update(['status' => 'approved']);
                    $count++;
                    break;

                case 'reject':
                    $document->update(['status' => 'rejected']);
                    $count++;
                    break;

                case 'delete':
                    if (Storage::disk('public')->exists($document->file_path)) {
                        Storage::disk('public')->delete($document->file_path);
                    }
                    $document->delete();
                    $count++;
                    break;
            }
        }

        return $this->successResponse([
            'processed' => $count,
            'message' => "{$count} documenti elaborati con successo"
        ]);
    }
}
