<?php

namespace EscolaLms\Courses\Jobs;

use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ActivateCourseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $courseService = app(CourseServiceContract::class);
        $courseService->activatePublishedCourses();
    }
}
