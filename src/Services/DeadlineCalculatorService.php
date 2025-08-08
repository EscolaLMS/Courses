<?php

namespace EscolaLms\Courses\Services;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Services\Contracts\DeadlineCalculatorServiceContract;
use Carbon\Carbon;

class DeadlineCalculatorService implements DeadlineCalculatorServiceContract
{

    public function calculate(Course $course, ?Carbon $startDate = null): ?Carbon
    {
        $start = $startDate ?? Carbon::now();
        $deadline = null;

        if (!is_null($course->hours_to_complete)) {
            $deadline = $start->copy()->addHours($course->hours_to_complete);
        }

        if (!is_null($course->active_to)) {
            if (is_null($deadline) || $course->active_to->lessThan($deadline)) {
                $deadline = $course->active_to;
            }
        }

        return $deadline;
    }
}
