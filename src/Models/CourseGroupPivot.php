<?php

namespace EscolaLms\Courses\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseGroupPivot extends Pivot
{
    protected $table = 'course_group';
}
