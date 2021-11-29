<?php

namespace EscolaLms\Courses\Listeners;

use EscolaLms\Courses\Events\DeadlineIncoming;
use EscolaLms\Courses\Notifications\DeadlineNotification;
use EscolaLms\Notifications\Facades\EscolaLmsNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeadlineIncomingListener implements ShouldQueue
{
    public function handle(DeadlineIncoming $event)
    {
        $user = $event->getUser();
        $course = $event->getCourse();

        $notification = EscolaLmsNotifications::findDatabaseNotification(DeadlineIncoming::class, $user, ['course_id' => $course->getKey()]);
        if (empty($notification)) {
            $user->notify(new DeadlineNotification($event->getCourse()));
        }
    }
}
