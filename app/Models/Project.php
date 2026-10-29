<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $guarded = []; // can change into fillable later on

    public function projectCards(): HasMany
    {
        return $this->hasMany(ProjectCard::class);
    }
}
