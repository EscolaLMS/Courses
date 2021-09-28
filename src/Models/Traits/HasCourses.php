<?php

namespace EscolaLms\Courses\Models\Traits;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseUserPivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasCourses
{
    public function courses(): BelongsToMany
    {
        /* @var $this \EscolaLms\Core\Models\User */
        return $this->belongsToMany(Course::class)->using(CourseUserPivot::class);
    }

    public function finishedCourse(int $id): bool
    {
        return $this->courses()->where('course_id', $id)->wherePivot('finished', true)->exists();
    }
}
