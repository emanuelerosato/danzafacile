<?php

namespace App\Http\Controllers\Admin;
use App\Http\Requests\UploadMediaGalleryRequest;
use App\Models\MediaGallery;
use App\Models\MediaItem;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MediaGalleryController extends AdminBaseController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Trova un media item in modo sicuro bypassando il global scope
     */
    private function findMediaItemSecurely(MediaGallery $gallery, $mediaItemId): MediaItem
    {
        // Bypass global scope usando la relazione della galleria
        $mediaItem = $gallery->mediaItems()->find($mediaItemId);

        // Controllo esplicito di sicurezza
        if (!$mediaItem || $mediaItem->mediaGallery->school_id !== $this->school->id) {
            abort(404, 'Media item non trovato o non autorizzato');
        }

        return $mediaItem;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->setupContext();

        $galleries = MediaGallery::forDashboard()
                                ->paginate(12);

        return view('admin.galleries.index', compact('galleries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->setupContext();

        $courses = Course::select('id', 'name')
                        ->bySchool($this->school->id)
                        ->active()
                        ->orderBy('name')
                        ->get();
        $galleryTypes = MediaGallery::getAvailableTypes();

        return view('admin.galleries.create', compact('courses', 'galleryTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->setupContext();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:' . implode(',', array_keys(MediaGallery::getAvailableTypes())),
            'course_id' => 'nullable|exists:courses,id',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $gallery = MediaGallery::create([
            'school_id' => $this->school->id,
            'course_id' => $request->course_id,
            'created_by' => $this->user->id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'is_public' => $request->boolean('is_public'),
            'is_featured' => $request->boolean('is_featured'),
            'settings' => [],
        ]);

        return redirect()->route('admin.galleries.show', $gallery)
                        ->with('success', 'Galleria creata con successo!');
    }

    /**
     * Display the specified resource.
     */
    public function show(MediaGallery $gallery)
    {
        $this->setupContext();

        $gallery->load([
            'course:id,name',
            'createdBy:id,name',
            'coverImage:id,gallery_id,file_path,thumbnail_url,type,title'
        ]);

        $mediaItems = $gallery->mediaItems()
                             ->with('user:id,name')
                             ->select('id', 'gallery_id', 'user_id', 'type', 'file_path', 'thumbnail_url', 'external_url', 'title', 'description', 'order', 'is_featured', 'created_at')
                             ->ordered()
                             ->paginate(20);

        return view('admin.galleries.show', compact('gallery', 'mediaItems'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MediaGallery $gallery)
    {
        $this->setupContext();

        $courses = Course::select('id', 'name')
                        ->bySchool($this->school->id)
                        ->active()
                        ->orderBy('name')
                        ->get();
        $galleryTypes = MediaGallery::getAvailableTypes();

        return view('admin.galleries.edit', compact('gallery', 'courses', 'galleryTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MediaGallery $gallery)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:' . implode(',', array_keys(MediaGallery::getAvailableTypes())),
            'course_id' => 'nullable|exists:courses,id',
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        $gallery->update([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'is_public' => $request->boolean('is_public'),
            'is_featured' => $request->boolean('is_featured'),
        ]);

        return redirect()->route('admin.galleries.show', $gallery)
                        ->with('success', 'Galleria aggiornata con successo!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MediaGallery $gallery)
    {
        // Elimina tutti i file fisici associati alla galleria
        foreach ($gallery->mediaItems as $mediaItem) {
            if ($mediaItem->is_file && $mediaItem->file_path) {
                Storage::disk('public')->delete($mediaItem->file_path);
            }
        }

        // Elimina la galleria (i media items saranno eliminati a cascata)
        $gallery->delete();

        return redirect()->route('admin.galleries.index')
                        ->with('success', 'Galleria eliminata con successo!');
    }

    /**
     * Upload media files to gallery
     */
    public function uploadMedia(UploadMediaGalleryRequest $request, MediaGallery $gallery)
    {
        // SECURITY: Validation with magic bytes check done in UploadMediaGalleryRequest

        $uploadedItems = [];
        $files = $request->file('files');

        foreach ($files as $index => $file) {
            $fileName = time() . '_' . $index . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('media/' . $gallery->id, $fileName, 'public');

            $mediaItem = MediaItem::create([
                'gallery_id' => $gallery->id,
                'user_id' => Auth::id(),
                'type' => MediaItem::TYPE_FILE,
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'title' => $request->input("title.{$index}") ?: $file->getClientOriginalName(),
                'description' => $request->input("description.{$index}"),
                'order' => MediaItem::getNextOrderForGallery($gallery->id),
                'is_featured' => false,
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toISOString()
                ]
            ]);

            $uploadedItems[] = $mediaItem->load('user:id,name');
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedItems) . ' file caricati con successo!',
            'items' => $uploadedItems
        ]);
    }

    /**
     * Add external link to gallery
     */
    public function addExternalLink(Request $request, MediaGallery $gallery)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url|max:500',
            'type' => 'required|in:external_link,youtube,vimeo,instagram',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errore di validazione',
                'errors' => $validator->errors()
            ], 422);
        }

        $externalId = null;
        $thumbnailUrl = null;

        // Estrai ID e thumbnail per YouTube e Vimeo
        if ($request->type === 'youtube') {
            $externalId = MediaItem::extractYouTubeId($request->url);
            if (!$externalId) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL YouTube non valido'
                ], 422);
            }
            $thumbnailUrl = "https://img.youtube.com/vi/{$externalId}/mqdefault.jpg";
        } elseif ($request->type === 'vimeo') {
            $externalId = MediaItem::extractVimeoId($request->url);
            if (!$externalId) {
                return response()->json([
                    'success' => false,
                    'message' => 'URL Vimeo non valido'
                ], 422);
            }
            // Per Vimeo, dovremmo chiamare l'API per ottenere la thumbnail
            // Ma per ora salviamo solo l'ID
        }

        $mediaItem = MediaItem::create([
            'gallery_id' => $gallery->id,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'external_url' => $request->url,
            'external_id' => $externalId,
            'thumbnail_url' => $thumbnailUrl,
            'title' => $request->title ?: 'Link Esterno',
            'description' => $request->description,
            'order' => MediaItem::getNextOrderForGallery($gallery->id),
            'is_featured' => false,
            'file_size' => 0, // I link esterni non hanno dimensione file
            'metadata' => [
                'added_at' => now()->toISOString(),
                'platform' => $request->type
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Link esterno aggiunto con successo!',
            'item' => $mediaItem->load('user:id,name')
        ]);
    }

    /**
     * Get media item data for editing
     */
    public function getMediaData(MediaGallery $gallery, $mediaItemId)
    {
        $this->setupContext();

        // Usa il metodo sicuro per trovare il media item
        $mediaItem = $this->findMediaItemSecurely($gallery, $mediaItemId);

        return $this->jsonResponse(true, '', [
            'media' => [
                'id' => $mediaItem->id,
                'title' => $mediaItem->title,
                'description' => $mediaItem->description,
                'is_featured' => $mediaItem->is_featured,
                'type' => $mediaItem->type,
                'order' => $mediaItem->order
            ]
        ]);
    }

    /**
     * Update media item
     */
    public function updateMediaItem(Request $request, MediaGallery $gallery, $mediaItemId)
    {
        $this->setupContext();

        // Usa il metodo sicuro per trovare il media item
        $mediaItem = $this->findMediaItemSecurely($gallery, $mediaItemId);

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse(false, 'Errore di validazione', [
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'is_featured' => $request->boolean('is_featured'),
        ];

        // Se è specificato un nuovo ordine, sposta l'elemento
        if ($request->has('order') && $request->order != $mediaItem->order) {
            $mediaItem->moveToPosition($request->order);
        }

        $mediaItem->update($updateData);

        // Ricarica il media item attraverso la galleria per assicurarsi che abbiamo i dati aggiornati
        $updatedMediaItem = $this->findMediaItemSecurely($gallery, $mediaItem->id);

        return $this->jsonResponse(true, 'Media aggiornato con successo!', [
            'item' => $updatedMediaItem
        ]);
    }

    /**
     * Delete media item
     */
    public function deleteMediaItem(MediaGallery $gallery, $mediaItemId)
    {
        $this->setupContext();

        // Usa il metodo sicuro per trovare il media item
        $mediaItem = $this->findMediaItemSecurely($gallery, $mediaItemId);

        // Verifica che l'utente possa eliminare questo media
        if (!$mediaItem->canBeDeletedBy($this->user)) {
            return $this->jsonResponse(false, 'Non hai i permessi per eliminare questo media', [], 403);
        }

        // Elimina il file fisico se presente
        if ($mediaItem->is_file && $mediaItem->file_path) {
            Storage::disk('public')->delete($mediaItem->file_path);
        }

        $mediaItem->delete();

        return $this->jsonResponse(true, 'Media eliminato con successo!');
    }

    /**
     * Set gallery cover image
     */
    public function setCoverImage(Request $request, MediaGallery $gallery)
    {
        $this->setupContext();

        $validator = Validator::make($request->all(), [
            'media_item_id' => 'required|exists:media_items,id',
        ]);

        if ($validator->fails()) {
            return $this->jsonResponse(false, 'Media item non valido', [], 422);
        }

        // Usa il metodo sicuro per trovare il media item
        $mediaItem = $this->findMediaItemSecurely($gallery, $request->media_item_id);

        $gallery->update(['cover_image_id' => $mediaItem->id]);

        return $this->jsonResponse(true, 'Immagine di copertina impostata con successo!');
    }
}
