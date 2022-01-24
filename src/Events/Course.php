<?php

namespace EscolaLms\Courses\Events;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course as CourseModel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class Course
{
    use Dispatchable, SerializesModels;

    private User $user;
    private CourseModel $course;

    public function __construct(User $user, CourseModel $course)
    {
        $this->user = $user;
        $this->course = $course;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCourse(): CourseModel
    {
        return $this->course;
    }
}
