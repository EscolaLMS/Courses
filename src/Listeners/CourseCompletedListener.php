<?php

namespace EscolaLms\Courses\Listeners;

use EscolaLms\Courses\Events\CourseCompleted;
use EscolaLms\Courses\Notifications\UserFinishedCourseNotification;
use EscolaLms\Notifications\Facades\EscolaLmsNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class CourseCompletedListener implements ShouldQueue
{
    public function handle(CourseCompleted $event)
    {
        $user = $event->getUser();
        $course = $event->getCourse();
        
        $notification = EscolaLmsNotifications::findDatabaseNotification(UserFinishedCourseNotification::class, $user, ['course_id' => $course->getKey()]);
        if (empty($notification)) {
            $user->notify(new UserFinishedCourseNotification($event->getCourse()));
        }
    }
}
