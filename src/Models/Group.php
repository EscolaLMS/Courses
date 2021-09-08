<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Auth\Models\Group as AuthGroup;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends AuthGroup
{
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->using(CourseGroupPivot::class);
    }
}
