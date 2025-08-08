<?php

namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Courses\Models\Course;
use Carbon\Carbon;

interface DeadlineCalculatorServiceContract
{
    public function calculate(Course $course, ?Carbon $startDate = null): ?Carbon;
}
