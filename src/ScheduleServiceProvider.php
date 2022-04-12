<?php

namespace EscolaLms\Courses;

use EscolaLms\Courses\Jobs\ActivateCourseJob;
use EscolaLms\Courses\Jobs\CheckForDeadlines;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->job(CheckForDeadlines::class)->hourly();
            $schedule->job(ActivateCourseJob::class)->daily();
        });
    }
}
