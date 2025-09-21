<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolRoom extends Model
{
    protected $fillable = [
        'school_id',
        'name',
        'description',
        'capacity',
        'equipment',
        'active'
    ];

    protected $casts = [
        'equipment' => 'array',
        'active' => 'boolean'
    ];

    /**
     * Get the school that owns this room
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope to get only active rooms
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to get rooms for a specific school
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
