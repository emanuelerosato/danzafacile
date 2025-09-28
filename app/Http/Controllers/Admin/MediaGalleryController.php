<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaGallery;
use App\Models\MediaItem;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MediaGalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $galleries = MediaGallery::with(['course:id,name', 'createdBy:id,name', 'mediaItems'])
                                ->withCount('mediaItems')
                                ->latest()
                                ->paginate(12);

        return view('admin.galleries.index', compact('galleries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $courses = Course::select('id', 'name')
                        ->bySchool(Auth::user()->school_id)
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
            'school_id' => Auth::user()->school_id,
            'course_id' => $request->course_id,
            'created_by' => Auth::id(),
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
        $gallery->load(['course:id,name', 'createdBy:id,name', 'coverImage']);

        $mediaItems = $gallery->mediaItems()
                             ->with('user:id,name')
                             ->ordered()
                             ->paginate(20);

        return view('admin.galleries.show', compact('gallery', 'mediaItems'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MediaGallery $gallery)
    {
        $courses = Course::select('id', 'name')
                        ->bySchool(Auth::user()->school_id)
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
    public function uploadMedia(Request $request, MediaGallery $gallery)
    {
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,bmp,webp,mp4,avi,mov,wmv,flv,3gp',
            'title.*' => 'nullable|string|max:255',
            'description.*' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errore di validazione',
                'errors' => $validator->errors()
            ], 422);
        }

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
        // Trova il media item attraverso la relazione della galleria per bypassare il global scope
        $mediaItem = $gallery->mediaItems()->find($mediaItemId);

        if (!$mediaItem) {
            abort(404);
        }

        return response()->json([
            'success' => true,
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
        // Trova il media item attraverso la relazione della galleria per bypassare il global scope
        $mediaItem = $gallery->mediaItems()->find($mediaItemId);

        if (!$mediaItem) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_featured' => 'boolean',
            'order' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errore di validazione',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'is_featured' => $request->boolean('is_featured'),
        ];

        \Log::info('Media update request', [
            'media_id' => $mediaItem->id,
            'request_data' => $request->all(),
            'update_data' => $updateData,
            'original_title' => $mediaItem->title,
            'original_description' => $mediaItem->description,
            'original_is_featured' => $mediaItem->is_featured
        ]);

        // Se è specificato un nuovo ordine, sposta l'elemento
        if ($request->has('order') && $request->order != $mediaItem->order) {
            $mediaItem->moveToPosition($request->order);
        }

        $updated = $mediaItem->update($updateData);

        \Log::info('Media update result', [
            'media_id' => $mediaItem->id,
            'update_success' => $updated,
            'new_title' => $mediaItem->title,
            'new_description' => $mediaItem->description,
            'new_is_featured' => $mediaItem->is_featured
        ]);

        // Ricarica il media item attraverso la galleria per assicurarsi che abbiamo i dati aggiornati
        $updatedMediaItem = $gallery->mediaItems()->find($mediaItem->id);

        return response()->json([
            'success' => true,
            'message' => 'Media aggiornato con successo!',
            'item' => $updatedMediaItem
        ]);
    }

    /**
     * Delete media item
     */
    public function deleteMediaItem(MediaGallery $gallery, $mediaItemId)
    {
        // Trova il media item attraverso la relazione della galleria per bypassare il global scope
        $mediaItem = $gallery->mediaItems()->find($mediaItemId);

        if (!$mediaItem) {
            abort(404);
        }

        // Verifica che l'utente possa eliminare questo media
        if (!$mediaItem->canBeDeletedBy(Auth::user())) {
            abort(403, 'Non hai i permessi per eliminare questo media');
        }

        // Elimina il file fisico se presente
        if ($mediaItem->is_file && $mediaItem->file_path) {
            Storage::disk('public')->delete($mediaItem->file_path);
        }

        $mediaItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Media eliminato con successo!'
        ]);
    }

    /**
     * Set gallery cover image
     */
    public function setCoverImage(Request $request, MediaGallery $gallery)
    {
        $validator = Validator::make($request->all(), [
            'media_item_id' => 'required|exists:media_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Media item non valido'
            ], 422);
        }

        $mediaItem = MediaItem::find($request->media_item_id);

        // Verifica che il media item appartenga alla galleria
        if ($mediaItem->gallery_id !== $gallery->id) {
            return response()->json([
                'success' => false,
                'message' => 'Il media non appartiene a questa galleria'
            ], 422);
        }

        $gallery->update(['cover_image_id' => $mediaItem->id]);

        return response()->json([
            'success' => true,
            'message' => 'Immagine di copertina impostata con successo!'
        ]);
    }
}
