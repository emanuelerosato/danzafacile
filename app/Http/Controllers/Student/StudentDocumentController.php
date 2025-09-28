<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentDocumentController extends Controller
{
    /**
     * Display student's documents
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Document::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $documents = $query->paginate(10);
        $categories = Document::getCategories();
        $statuses = Document::getStatuses();

        return view('student.documents.index', compact('documents', 'categories', 'statuses'));
    }

    /**
     * Show form for uploading new document
     */
    public function create()
    {
        $categories = Document::getCategories();
        return view('student.documents.create', compact('categories'));
    }

    /**
     * Store new document
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'category' => 'required|in:' . implode(',', array_keys(Document::getCategories())),
        ]);

        $file = $request->file('file');
        $filename = Str::slug($request->name) . '-' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('documents/' . $user->school_id . '/' . $user->id, $filename, 'private');

        Document::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'name' => $request->name,
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'category' => $request->category,
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);

        return redirect()->route('student.documents.index')
            ->with('success', 'Documento caricato con successo e in attesa di approvazione');
    }

    /**
     * Display document details
     */
    public function show(Document $document)
    {
        $user = auth()->user();

        // Students can only view their own documents
        if ($document->user_id !== $user->id) {
            abort(403, 'Non autorizzato');
        }

        return view('student.documents.show', compact('document'));
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        $user = auth()->user();

        // Students can only download their own documents
        if ($document->user_id !== $user->id) {
            abort(403, 'Non autorizzato');
        }

        if (!Storage::disk('private')->exists($document->file_path)) {
            abort(404, 'File non trovato');
        }

        return Storage::disk('private')->download($document->file_path, $document->name . '.' . $document->file_type);
    }

    /**
     * Remove document
     */
    public function destroy(Document $document)
    {
        $user = auth()->user();

        // Students can only delete their own pending documents
        if ($document->user_id !== $user->id) {
            abort(403, 'Non autorizzato');
        }

        if ($document->status !== 'pending') {
            return redirect()->route('student.documents.index')
                ->with('error', 'Non puoi eliminare documenti già approvati o rifiutati');
        }

        // Delete file
        if (Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('student.documents.index')
            ->with('success', 'Documento eliminato con successo');
    }
}
