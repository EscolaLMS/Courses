<?php

namespace EscolalLms\Courses\Listeners;

use EscolaLms\Courses\Events\CourseCompleted;
use EscolaLms\Courses\Models\CourseUserPivot;
use EscolaLms\Courses\Notifications\UserFinishedCourseNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class CourseCompletedListener implements ShouldQueue
{
    public function handle(CourseCompleted $event)
    {
        $user = $event->getUser();
        $course = $event->getCourse();
        $pivot = CourseUserPivot::where('user_id', $user->getKey())->where('course_id', $course->getKey())->first();
        if (!$pivot->course_completed_notification) {
            $pivot->course_completed_notification = true;
            $pivot->save();
            $user->notify(new UserFinishedCourseNotification($event->getCourse()));
        }
    }
}
