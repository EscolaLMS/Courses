<?php

namespace EscolaLms\Courses\Listeners;

use EscolaLms\Courses\Events\DeadlineIncoming;
use EscolaLms\Courses\Notifications\DeadlineNotification;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use EscolaLms\Notifications\Facades\EscolaLmsNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeadlineIncomingListener implements ShouldQueue
{
    public function handle(DeadlineIncoming $event)
    {
        $user = $event->getUser();
        $course = $event->getCourse();

        $notification = EscolaLmsNotifications::findDatabaseNotification(DeadlineNotification::class, $user, ['course_id' => $course->getKey()]);
        if (!empty($notification)) {
            return;
        }
        $progress = CourseProgressCollection::make($user, $course);
        if (!$progress->isFinished()) {
            $user->notify(new DeadlineNotification($course));
        }
    }
}
