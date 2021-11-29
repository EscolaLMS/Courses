<?php

namespace EscolaLms\Courses\Listeners;

use EscolaLms\Courses\Events\DeadlineIncoming;
use EscolaLms\Courses\Models\CourseUserPivot;
use EscolaLms\Courses\Notifications\DeadlineNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeadlineIncomingListener implements ShouldQueue
{
    public function handle(DeadlineIncoming $event)
    {
        $user = $event->getUser();
        $course = $event->getCourse();
        $pivot = CourseUserPivot::where('user_id', $user->getKey())->where('course_id', $course->getKey())->first();
        if (!$pivot) {
            $course->users()->attach($user);
            $pivot = CourseUserPivot::where('user_id', $user->getKey())->where('course_id', $course->getKey())->first();
        }
        if (!$pivot->deadline_notification) {
            $pivot->deadline_notification = true;
            $pivot->save();
            $user->notify(new DeadlineNotification($event->getCourse()));
        }
    }
}
