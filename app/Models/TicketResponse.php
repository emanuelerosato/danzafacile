<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketResponse extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachments',
        'is_internal'
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean'
    ];

    /**
     * Relationship with Ticket
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Relationship with User (response author)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted time for UI
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Get time ago for UI
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if user can attach images (Super Admin only)
     */
    public function canAttachImages(): bool
    {
        return $this->user && $this->user->role === 'super_admin';
    }

    /**
     * Get attachment URLs
     */
    public function getAttachmentUrlsAttribute(): array
    {
        if (!$this->attachments) {
            return [];
        }

        return array_map(function($attachment) {
            return asset('storage/' . $attachment);
        }, $this->attachments);
    }

    /**
     * Scope for public responses (not internal)
     */
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    /**
     * Scope for internal responses
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }
}
