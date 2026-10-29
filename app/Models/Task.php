<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\BelongsTo;

class Task extends Model
{
    protected $guarded = [];

    public function projectCard(): BelongsTo
    {
        return $this->belongsTo(ProjectCard::class);
    }
}
