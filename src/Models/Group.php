<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Auth\Models\Group as AuthGroup;
use EscolaLms\Courses\Database\Factories\GroupFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends AuthGroup
{
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->using(CourseGroupPivot::class)->withTimestamps();
    }

    protected static function newFactory()
    {
        return new GroupFactory();
    }
}
