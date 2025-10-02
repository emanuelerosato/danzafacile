<?php

namespace App\Http\Controllers\API;

use App\Models\MediaGallery;
use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends BaseApiController
{
    /**
     * Get galleries list
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = MediaGallery::with(['mediaItems', 'createdBy']);

        // Role-based filtering
        if ($user->isAdmin()) {
            // Admin sees all galleries of their school
            $query->where('school_id', $user->school_id);
        } elseif ($user->isStudent()) {
            // Students see only public galleries of their school
            $query->where('school_id', $user->school_id)
                  ->where('is_public', true);
        } else {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Filters
        if ($request->filled('is_public') && $user->isAdmin()) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        // Search
        if ($request->filled('search')) {
            $search = \App\Helpers\QueryHelper::sanitizeLikeInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $galleries = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'galleries' => $galleries->items(),
            'pagination' => [
                'current_page' => $galleries->currentPage(),
                'total_pages' => $galleries->lastPage(),
                'per_page' => $galleries->perPage(),
                'total' => $galleries->total(),
            ]
        ]);
    }

    /**
     * Get gallery statistics (admin only)
     */
    public function statistics(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono visualizzare le statistiche', 403);
        }

        $query = MediaGallery::where('school_id', $user->school_id);

        $stats = [
            'total' => $query->count(),
            'public' => (clone $query)->where('is_public', true)->count(),
            'private' => (clone $query)->where('is_public', false)->count(),
            'total_media' => MediaItem::whereHas('gallery', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })->count(),
        ];

        return $this->successResponse($stats);
    }

    /**
     * Create new gallery (admin only)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono creare gallerie', 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $gallery = MediaGallery::create([
            'school_id' => $user->school_id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_public' => $validated['is_public'] ?? true,
        ]);

        $gallery->load(['mediaItems', 'createdBy']);

        return $this->successResponse([
            'gallery' => $gallery,
            'message' => 'Galleria creata con successo'
        ], 201);
    }

    /**
     * Show gallery details
     */
    public function show($id)
    {
        $user = Auth::user();

        $gallery = MediaGallery::with(['mediaItems', 'createdBy'])->find($id);

        if (!$gallery) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        // Check authorization
        if ($gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isStudent() && !$gallery->is_public) {
            return $this->errorResponse('Galleria non pubblica', 403);
        }

        return $this->successResponse(['gallery' => $gallery]);
    }

    /**
     * Update gallery (admin only)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono aggiornare le gallerie', 403);
        }

        $gallery = MediaGallery::find($id);

        if (!$gallery) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        if ($gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'nullable|boolean',
        ]);

        $gallery->update(array_filter($validated, function($value) {
            return !is_null($value);
        }));

        return $this->successResponse([
            'gallery' => $gallery->fresh(['mediaItems', 'createdBy']),
            'message' => 'Galleria aggiornata con successo'
        ]);
    }

    /**
     * Delete gallery (admin only)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono eliminare le gallerie', 403);
        }

        $gallery = MediaGallery::find($id);

        if (!$gallery) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        if ($gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        // Delete all media items and their files
        foreach ($gallery->mediaItems as $mediaItem) {
            if ($mediaItem->type !== 'external_link' && Storage::disk('public')->exists($mediaItem->file_path)) {
                Storage::disk('public')->delete($mediaItem->file_path);
            }
            $mediaItem->delete();
        }

        $gallery->delete();

        return $this->successResponse([
            'message' => 'Galleria eliminata con successo'
        ]);
    }

    /**
     * Get media items of a gallery
     */
    public function getMedia($id)
    {
        $user = Auth::user();

        $gallery = MediaGallery::with('mediaItems')->find($id);

        if (!$gallery) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        if ($gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isStudent() && !$gallery->is_public) {
            return $this->errorResponse('Galleria non pubblica', 403);
        }

        return $this->successResponse([
            'media' => $gallery->mediaItems
        ]);
    }

    /**
     * Upload media to gallery (admin only)
     */
    public function uploadMedia(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono caricare media', 403);
        }

        $gallery = MediaGallery::find($id);

        if (!$gallery) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        if ($gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480', // 20MB
        ]);

        $file = $request->file('file');

        // Validate file
        $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'mov', 'avi'];
        $validation = \App\Helpers\FileUploadHelper::validateFile($file, $allowedMimes, 20480);

        if (!$validation['valid']) {
            return $this->errorResponse($validation['error'], 422);
        }

        // Determine type
        $mimeType = $file->getMimeType();
        $type = str_starts_with($mimeType, 'image/') ? 'image' : 'video';

        // Store file
        $path = $file->store('galleries', 'public');

        // Create media item
        $mediaItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'type' => $type,
            'title' => $validated['title'] ?? $file->getClientOriginalName(),
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
        ]);

        return $this->successResponse([
            'media' => $mediaItem,
            'message' => 'Media caricato con successo'
        ], 201);
    }

    /**
     * Add external link to gallery (admin only)
     */
    public function addExternalLink(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono aggiungere link esterni', 403);
        }

        $gallery = MediaGallery::find($id);

        if (!$gallery) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        if ($gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:500',
            'description' => 'nullable|string',
        ]);

        $mediaItem = MediaItem::create([
            'media_gallery_id' => $gallery->id,
            'type' => 'external_link',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'external_url' => $validated['url'],
        ]);

        return $this->successResponse([
            'media' => $mediaItem,
            'message' => 'Link esterno aggiunto con successo'
        ], 201);
    }

    /**
     * Update media item (admin only)
     */
    public function updateMedia(Request $request, $galleryId, $mediaId)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono aggiornare i media', 403);
        }

        $gallery = MediaGallery::find($galleryId);

        if (!$gallery || $gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        $mediaItem = MediaItem::where('media_gallery_id', $galleryId)->find($mediaId);

        if (!$mediaItem) {
            return $this->errorResponse('Media non trovato', 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $mediaItem->update(array_filter($validated, function($value) {
            return !is_null($value);
        }));

        return $this->successResponse([
            'media' => $mediaItem,
            'message' => 'Media aggiornato con successo'
        ]);
    }

    /**
     * Delete media item (admin only)
     */
    public function deleteMedia($galleryId, $mediaId)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono eliminare i media', 403);
        }

        $gallery = MediaGallery::find($galleryId);

        if (!$gallery || $gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        $mediaItem = MediaItem::where('media_gallery_id', $galleryId)->find($mediaId);

        if (!$mediaItem) {
            return $this->errorResponse('Media non trovato', 404);
        }

        // Delete file if not external link
        if ($mediaItem->type !== 'external_link' && Storage::disk('public')->exists($mediaItem->file_path)) {
            Storage::disk('public')->delete($mediaItem->file_path);
        }

        $mediaItem->delete();

        return $this->successResponse([
            'message' => 'Media eliminato con successo'
        ]);
    }

    /**
     * Set cover image for gallery (admin only)
     */
    public function setCoverImage(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono impostare la copertina', 403);
        }

        $gallery = MediaGallery::find($id);

        if (!$gallery || $gallery->school_id !== $user->school_id) {
            return $this->errorResponse('Galleria non trovata', 404);
        }

        $validated = $request->validate([
            'media_id' => 'required|exists:media_items,id',
        ]);

        $mediaItem = MediaItem::where('media_gallery_id', $id)
            ->where('id', $validated['media_id'])
            ->where('type', 'image')
            ->first();

        if (!$mediaItem) {
            return $this->errorResponse('Media non valido o non trovato', 404);
        }

        $gallery->update(['cover_image_id' => $mediaItem->id]);

        return $this->successResponse([
            'gallery' => $gallery->fresh(['mediaItems', 'createdBy']),
            'message' => 'Copertina impostata con successo'
        ]);
    }
}
