<?php


namespace EscolaLms\Courses\Events;


use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Events\Contracts\BadgeEventContract;
use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourseCompleted implements BadgeEventContract
{
    use Dispatchable, SerializesModels;

    private Course $course;
    private User $user;

    public function __construct(Course $course, User $user)
    {
        $this->course = $course;
        $this->user = $user;
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
