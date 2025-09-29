<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSchoolScope
{
    /**
     * Boot the trait and add school scoping
     */
    protected static function bootHasSchoolScope(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            // Usa session o service container invece di auth()->check()
            $currentSchoolId = app()->bound('current_school_id') ? app('current_school_id') : session('current_school_id');

            if ($currentSchoolId) {
                $builder->where($builder->getModel()->getTable() . '.school_id', $currentSchoolId);
            }
        });
    }

    /**
     * Scope query to current school
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where($this->getTable() . '.school_id', $schoolId);
    }

    /**
     * Scope query without school restriction (admin use)
     */
    public function scopeWithoutSchoolScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('school');
    }

    /**
     * Get current school ID from context
     */
    public static function getCurrentSchoolId(): ?int
    {
        return app()->bound('current_school_id') ? app('current_school_id') : session('current_school_id');
    }

    /**
     * Create a new instance for current school
     */
    public static function createForCurrentSchool(array $attributes = []): static
    {
        $schoolId = static::getCurrentSchoolId();

        if ($schoolId && !isset($attributes['school_id'])) {
            $attributes['school_id'] = $schoolId;
        }

        return static::create($attributes);
    }
}