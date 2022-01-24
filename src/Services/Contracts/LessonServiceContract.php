<?php

namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Courses\Models\Lesson;
use Illuminate\Database\Eloquent\Model;

interface LessonServiceContract
{
    public function cloneLesson(Lesson $lesson): Model;
}
