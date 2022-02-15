<?php

namespace EscolaLms\Courses\Models;

use EscolaLms\Courses\Models\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseGroupPivot extends Pivot
{
    use ClearsResponseCache;

    protected $table = 'course_group';
}
