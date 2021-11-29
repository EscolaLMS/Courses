<?php

namespace EscolaLms\Courses\Jobs;

use EscolaLms\Courses\Events\DeadlineIncoming;
use EscolaLms\Courses\Models\CourseUserPivot;
use EscolaLms\Courses\Notifications\DeadlineNotification;
use EscolaLms\Notifications\Facades\EscolaLmsNotifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\Job;
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
        $futureDeadlines = CourseUserPivot::whereDate('deadline', '>=', Carbon::now())->whereDate('deadline', '<=', Carbon::now()->addDay())->get();
        foreach ($futureDeadlines as $courseUserPivot) {
            event(new DeadlineIncoming($courseUserPivot->user, $courseUserPivot->course));
        }
    }
}
