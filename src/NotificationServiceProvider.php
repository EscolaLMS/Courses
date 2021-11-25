<?php

namespace EscolaLms\Courses;

use EscolaLms\Courses\Notifications\DeadlineNotification;
use EscolaLms\Courses\Notifications\UserAssignedToCourseNotification;
use EscolaLms\Courses\Notifications\UserFinishedCourseNotification;
use EscolaLms\Courses\Notifications\UserUnassignedFromCourseNotification;
use EscolaLms\Notifications\Facades\EscolaLmsNotifications;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // this wont work if database wasn't migrated, need to change registerNotification to not use database; creating default templates should be separate method called in seeder or something
        EscolaLmsNotifications::registerNotification(DeadlineNotification::class);
        EscolaLmsNotifications::registerNotification(UserAssignedToCourseNotification::class);
        EscolaLmsNotifications::registerNotification(UserFinishedCourseNotification::class);
        EscolaLmsNotifications::registerNotification(UserUnassignedFromCourseNotification::class);
    }
}
