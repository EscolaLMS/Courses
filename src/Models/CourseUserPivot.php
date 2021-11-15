<?php

namespace EscolaLms\Courses\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseUserPivot extends Pivot
{
    protected $table = 'course_user';

    protected $casts = [
        'deadline' => 'datetime'
    ];
}
