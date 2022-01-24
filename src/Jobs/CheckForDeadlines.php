<?php

namespace EscolaLms\Courses\Jobs;

use EscolaLms\Courses\Events\CourseDeadlineSoon;
use EscolaLms\Courses\Models\CourseUserPivot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CheckForDeadlines implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Collection $futureDeadlines */
        $finishDate = Carbon::now()->modify('+ ' . config('escolalms_courses.reminder_of_deadline_count_days') . ' days')->format('Y-m-d');
        $futureDeadlines = CourseUserPivot::whereDate('deadline', '>=', Carbon::now())->whereDate('deadline', '=', $finishDate)->get();
        foreach ($futureDeadlines as $courseUserPivot) {
            event(new CourseDeadlineSoon($courseUserPivot->user, $courseUserPivot->course));
        }
    }
}
