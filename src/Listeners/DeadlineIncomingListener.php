<?php

namespace EscolalLms\Courses\Listeners;

use EscolaLms\Courses\Events\CourseCompleted;
use EscolaLms\Courses\Models\CourseUserPivot;
use EscolaLms\Courses\Notifications\DeadlineNotification;

class DeadlineIncomingListener
{
    public function __construct()
    {
    }

    public function handle(CourseCompleted $event)
    {
        $user = $event->getUser();
        $course = $event->getCourse();
        $pivot = CourseUserPivot::where('user_id', $user->getKey())->where('course_id', $course->getKey())->first();
        if (!$pivot->deadline_notification) {
            $pivot->deadline_notification = true;
            $pivot->save();
            $user->notify(new DeadlineNotification($event->getCourse()));
        }
    }
}
