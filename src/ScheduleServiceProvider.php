<?php

namespace EscolaLms\Courses;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->job(CheckForDeadlines::class)->hourly();
        });
    }
}
