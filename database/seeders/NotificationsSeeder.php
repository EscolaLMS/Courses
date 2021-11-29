<?php

namespace EscolaLms\Courses\Database\Seeders;

use EscolaLms\Courses\Notifications\DeadlineNotification;
use EscolaLms\Courses\Notifications\UserAssignedToCourseNotification;
use EscolaLms\Courses\Notifications\UserFinishedCourseNotification;
use EscolaLms\Courses\Notifications\UserUnassignedFromCourseNotification;
use EscolaLms\Notifications\Facades\EscolaLmsNotifications;
use Illuminate\Database\Seeder;

class NotificationsSeeder extends Seeder
{
    public function run()
    {
        EscolaLmsNotifications::createDefaultTemplates(DeadlineNotification::class);
        EscolaLmsNotifications::createDefaultTemplates(UserAssignedToCourseNotification::class);
        EscolaLmsNotifications::createDefaultTemplates(UserFinishedCourseNotification::class);
        EscolaLmsNotifications::createDefaultTemplates(UserUnassignedFromCourseNotification::class);
    }
}
