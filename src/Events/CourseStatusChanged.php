<?php

namespace EscolaLms\Courses\Events;

use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseStatusChanged
{
    use Dispatchable, SerializesModels;

    private Course $course;

    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }
}
