<?php


namespace EscolaLms\Courses\Models\Traits;


use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasCourses
{
    public function courses(): BelongsToMany
    {
        /* @var $this User */
        return $this->belongsToMany(Course::class)->withTimestamps();
    }
}