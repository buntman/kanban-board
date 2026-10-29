<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\HasMany;
use Illuminate\Database\Eloquent\BelongsTo;

class ProjectCard extends Model
{
    protected $guarded = [];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
