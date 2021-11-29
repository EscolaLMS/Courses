<?php

namespace EscolaLms\Courses\Jobs;

use EscolaLms\Courses\Events\DeadlineIncoming;
use EscolaLms\Courses\Models\CourseUserPivot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

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
        $futureDeadlines = CourseUserPivot::whereDate('deadline', '>=', Carbon::now())->where('deadline_notification', false)->get();
        foreach ($futureDeadlines as $courseUserPivot) {
            if (Carbon::parse($courseUserPivot->deadline)->subDay()->lessThan(Carbon::now())) {
                event(new DeadlineIncoming($courseUserPivot->user, $courseUserPivot->course));
            }
        }
    }
}
