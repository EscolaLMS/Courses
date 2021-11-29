<?php

namespace EscolaLms\Courses\Database\Seeders;

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
