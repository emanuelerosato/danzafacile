<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadMediaItemRequest;
use App\Http\Requests\UpdateMediaItemRequest;
use App\Models\MediaItem;
use App\Models\MediaGallery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaItemController extends Controller
{
    /**
     * Display a listing of media items
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = MediaItem::with(['user', 'gallery']);

        // Role-based filtering
        if ($user->isSuperAdmin()) {
            // Super admin can see all media
        } elseif ($user->isAdmin()) {
            // Admin can see media from their school
            $query->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        } else {
            // Students see only their own media
            $query->where('user_id', $user->id);
        }

        // Filter by gallery
        if ($request->filled('gallery_id')) {
            $query->where('gallery_id', $request->get('gallery_id'));
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $mediaItems = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get galleries for filter
        $galleries = collect();
        if ($user->isSuperAdmin()) {
            $galleries = MediaGallery::orderBy('name')->get();
        } elseif ($user->isAdmin()) {
            $galleries = MediaGallery::whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })->orderBy('name')->get();
        } else {
            $galleries = MediaGallery::where('user_id', $user->id)->orderBy('name')->get();
        }

        if ($request->ajax()) {
            return response()->json([
                'html' => view('shared.media.partials.grid', compact('mediaItems'))->render(),
                'pagination' => $mediaItems->links()->render()
            ]);
        }

        return view('shared.media.index', compact('mediaItems', 'galleries'));
    }

    /**
     * Show the form for creating a new media item
     */
    public function create()
    {
        $user = auth()->user();
        
        // Get available galleries
        $galleries = collect();
        if ($user->isSuperAdmin()) {
            $galleries = MediaGallery::orderBy('name')->get();
        } elseif ($user->isAdmin()) {
            $galleries = MediaGallery::whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })->orderBy('name')->get();
        } else {
            $galleries = MediaGallery::where('user_id', $user->id)->orderBy('name')->get();
        }

        return view('shared.media.create', compact('galleries'));
    }

    /**
     * Store a newly created media item
     */
    public function store(UploadMediaItemRequest $request)
    {
        // SECURITY: Validation with magic bytes check done in UploadMediaItemRequest

        $user = auth()->user();
        
        // Check gallery ownership
        $gallery = MediaGallery::findOrFail($request->gallery_id);
        $this->authorizeGallery($gallery);

        // Handle file upload
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('media', $filename, 'public');

        $mediaItem = MediaItem::create([
            'user_id' => $user->id,
            'gallery_id' => $request->gallery_id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'type' => $request->type,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Media caricato con successo.',
                'media' => $mediaItem->load(['user', 'gallery'])
            ]);
        }

        return redirect()->route('media.index')
                        ->with('success', 'Media caricato con successo.');
    }

    /**
     * Display the specified media item
     */
    public function show(MediaItem $mediaItem)
    {
        $this->authorizeMediaItem($mediaItem);

        $mediaItem->load(['user', 'gallery']);

        return view('shared.media.show', compact('mediaItem'));
    }

    /**
     * Show the form for editing the specified media item
     */
    public function edit(MediaItem $mediaItem)
    {
        $this->authorizeMediaItem($mediaItem);

        $user = auth()->user();
        
        // Get available galleries
        $galleries = collect();
        if ($user->isSuperAdmin()) {
            $galleries = MediaGallery::orderBy('name')->get();
        } elseif ($user->isAdmin()) {
            $galleries = MediaGallery::whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            })->orderBy('name')->get();
        } else {
            $galleries = MediaGallery::where('user_id', $user->id)->orderBy('name')->get();
        }

        return view('shared.media.edit', compact('mediaItem', 'galleries'));
    }

    /**
     * Update the specified media item
     */
    public function update(UpdateMediaItemRequest $request, MediaItem $mediaItem)
    {
        $this->authorizeMediaItem($mediaItem);

        // SECURITY: Validation with magic bytes check done in UpdateMediaItemRequest

        // Check gallery ownership for new gallery
        if ($request->gallery_id != $mediaItem->gallery_id) {
            $newGallery = MediaGallery::findOrFail($request->gallery_id);
            $this->authorizeGallery($newGallery);
        }

        $data = $request->except(['file']);

        // Handle file replacement
        if ($request->hasFile('file')) {
            // Delete old file
            if ($mediaItem->file_path) {
                Storage::disk('public')->delete($mediaItem->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('media', $filename, 'public');
            $data['file_name'] = $file->getClientOriginalName();
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        }

        $mediaItem->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Media aggiornato con successo.',
                'media' => $mediaItem->fresh()->load(['user', 'gallery'])
            ]);
        }

        return redirect()->route('media.show', $mediaItem)
                        ->with('success', 'Media aggiornato con successo.');
    }

    /**
     * Remove the specified media item
     */
    public function destroy(MediaItem $mediaItem)
    {
        $this->authorizeMediaItem($mediaItem);

        // Delete file from storage
        if ($mediaItem->file_path) {
            Storage::disk('public')->delete($mediaItem->file_path);
        }

        $mediaItem->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Media eliminato con successo.'
            ]);
        }

        return redirect()->route('media.index')
                        ->with('success', 'Media eliminato con successo.');
    }

    /**
     * Display media item in lightbox/modal
     */
    public function view(MediaItem $mediaItem)
    {
        $this->authorizeMediaItem($mediaItem);

        $mediaItem->load(['user', 'gallery']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'media' => $mediaItem,
                'html' => view('shared.media.partials.viewer', compact('mediaItem'))->render()
            ]);
        }

        return redirect()->route('media.show', $mediaItem);
    }

    /**
     * Download media file
     */
    public function download(MediaItem $mediaItem)
    {
        $this->authorizeMediaItem($mediaItem);

        if (!$mediaItem->file_path || !Storage::disk('public')->exists($mediaItem->file_path)) {
            abort(404, 'File non trovato.');
        }

        return Storage::disk('public')->download($mediaItem->file_path, $mediaItem->file_name);
    }

    /**
     * Get media items for a specific gallery (AJAX)
     */
    public function getByGallery(MediaGallery $gallery)
    {
        $this->authorizeGallery($gallery);

        $mediaItems = $gallery->mediaItems()
                             ->orderBy('created_at', 'desc')
                             ->get();

        return response()->json([
            'success' => true,
            'gallery' => $gallery,
            'media_items' => $mediaItems,
            'html' => view('shared.media.partials.gallery-grid', compact('mediaItems'))->render()
        ]);
    }

    /**
     * Bulk actions for media items
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,move_gallery',
            'media_ids' => 'required|array',
            'media_ids.*' => 'exists:media_items,id',
            'target_gallery_id' => 'required_if:action,move_gallery|exists:media_galleries,id'
        ]);

        $user = auth()->user();
        $mediaIds = $request->get('media_ids');
        $action = $request->get('action');

        // Get media items with authorization check
        $query = MediaItem::whereIn('id', $mediaIds);

        if ($user->isAdmin()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        } elseif ($user->isStudent()) {
            $query->where('user_id', $user->id);
        }

        $mediaItems = $query->get();

        foreach ($mediaItems as $mediaItem) {
            switch ($action) {
                case 'delete':
                    if ($mediaItem->file_path) {
                        Storage::disk('public')->delete($mediaItem->file_path);
                    }
                    $mediaItem->delete();
                    break;

                case 'move_gallery':
                    $targetGallery = MediaGallery::findOrFail($request->target_gallery_id);
                    $this->authorizeGallery($targetGallery);
                    $mediaItem->update(['gallery_id' => $request->target_gallery_id]);
                    break;
            }
        }

        $message = match($action) {
            'delete' => 'Media eliminati con successo.',
            'move_gallery' => 'Media spostati nella nuova galleria.',
        };

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get media statistics
     */
    public function getStatistics()
    {
        $user = auth()->user();
        $query = MediaItem::query();

        // Role-based filtering
        if ($user->isAdmin()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        } elseif ($user->isStudent()) {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total_items' => $query->count(),
            'total_size' => $query->sum('file_size'),
            'by_type' => $query->selectRaw('type, count(*) as count')
                              ->groupBy('type')
                              ->pluck('count', 'type'),
            'recent_uploads' => $query->where('created_at', '>=', now()->subWeek())
                                    ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Check if user can access/modify media item
     */
    private function authorizeMediaItem(MediaItem $mediaItem)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isAdmin()) {
            if ($mediaItem->user->school_id !== $user->school_id) {
                abort(403, 'Non autorizzato');
            }
            return;
        }

        if ($user->isStudent()) {
            if ($mediaItem->user_id !== $user->id) {
                abort(403, 'Non autorizzato');
            }
            return;
        }

        abort(403, 'Non autorizzato');
    }

    /**
     * Check if user can access/modify gallery
     */
    private function authorizeGallery(MediaGallery $gallery)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->isAdmin()) {
            if ($gallery->user->school_id !== $user->school_id) {
                abort(403, 'Non autorizzato');
            }
            return;
        }

        if ($user->isStudent()) {
            if ($gallery->user_id !== $user->id) {
                abort(403, 'Non autorizzato');
            }
            return;
        }

        abort(403, 'Non autorizzato');
    }
}