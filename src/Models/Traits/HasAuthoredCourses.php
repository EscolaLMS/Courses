<?php

namespace EscolaLms\Courses\Models\Traits;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseAuthorPivot;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasAuthoredCourses
{
    public function authoredCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_author', 'author_id', 'course_id')->using(CourseAuthorPivot::class);
    }
}
