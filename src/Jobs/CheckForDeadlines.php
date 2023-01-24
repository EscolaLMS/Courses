<?php

namespace EscolaLms\Courses\Jobs;

use EscolaLms\Courses\Events\CourseDeadlineSoon;
use EscolaLms\Courses\Models\CourseUserPivot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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
        $startDate = Carbon::now()
            ->modify('+ ' . config('escolalms_courses.reminder_of_deadline_count_days') . ' days')
            ->subMinutes(30);
        $endDate = Carbon::now()
            ->modify('+ ' . config('escolalms_courses.reminder_of_deadline_count_days') . ' days')
            ->addMinutes(30);
        $futureDeadlines = CourseUserPivot::where('deadline', '>=', $startDate)
            ->where('deadline', '<', $endDate)
            ->get();

        Log::debug('Checking for deadlines');

        foreach ($futureDeadlines as $courseUserPivot) {
            event(new CourseDeadlineSoon($courseUserPivot->user, $courseUserPivot->course));
        }
    }
}
