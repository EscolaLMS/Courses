<?php

namespace EscolaLms\Courses\Events;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Lesson;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LessonFinished
{
    use Dispatchable, SerializesModels;

    private User $user;
    private Lesson $lesson;

    public function __construct(User $user, Lesson $lesson)
    {
        $this->user = $user;
        $this->lesson = $lesson;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getLesson(): Lesson
    {
        return $this->lesson;
    }
}
