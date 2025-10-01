<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Ticket extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'category',
        'user_id',
        'assigned_to',
        'closed_at'
    ];

    protected $casts = [
        'closed_at' => 'datetime'
    ];

    /**
     * Relationship with User (ticket creator)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with User (assigned to)
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relationship with TicketResponse
     */
    public function responses(): HasMany
    {
        return $this->hasMany(TicketResponse::class)->orderBy('created_at', 'asc');
    }

    /**
     * Scope for open tickets
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for pending tickets
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for closed tickets
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Scope for high priority tickets
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'bg-green-100 text-green-800 border-green-200',
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'closed' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-blue-100 text-blue-800 border-blue-200'
        };
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'critical' => 'bg-red-100 text-red-800 border-red-200',
            'high' => 'bg-orange-100 text-orange-800 border-orange-200',
            'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'low' => 'bg-green-100 text-green-800 border-green-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200'
        };
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
     * Check if ticket is overdue (open for more than 48 hours)
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'open' && $this->created_at->diffInHours(now()) > 48;
    }

    /**
     * Get response count
     */
    public function getResponseCountAttribute(): int
    {
        return $this->responses()->count();
    }

    /**
     * Get ticket direction for Admin view
     * Returns 'sent' if admin created ticket (to SuperAdmin)
     * Returns 'received' if student created ticket (from Students)
     */
    public function getDirectionAttribute(): string
    {
        // If creator is admin, ticket is sent TO SuperAdmin
        if ($this->user && $this->user->role === 'admin') {
            return 'sent';
        }

        // If creator is student/user, ticket is received FROM student
        return 'received';
    }

    /**
     * Get direction badge for UI
     */
    public function getDirectionBadgeAttribute(): array
    {
        if ($this->direction === 'sent') {
            return [
                'text' => 'Inviato a SuperAdmin',
                'icon' => 'arrow-up',
                'color' => 'bg-purple-100 text-purple-800 border-purple-200'
            ];
        }

        return [
            'text' => 'Ricevuto da Studente',
            'icon' => 'arrow-down',
            'color' => 'bg-blue-100 text-blue-800 border-blue-200'
        ];
    }
}
