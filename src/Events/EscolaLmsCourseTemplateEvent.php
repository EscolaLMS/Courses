<?php

namespace EscolaLms\Courses\Events;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class EscolaLmsCourseTemplateEvent
{
    use Dispatchable, SerializesModels;

    private User $user;
    private Course $course;

    public function __construct(User $user, Course $course)
    {
        $this->user = $user;
        $this->course = $course;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCourse(): Course
    {
        return $this->course;
    }
}
