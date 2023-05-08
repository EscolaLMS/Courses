<?php

namespace EscolaLms\Courses\Database\Factories;

use EscolaLms\Courses\Models\CourseUserAttendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseUserAttendanceFactory extends Factory
{
    protected $model = CourseUserAttendance::class;

    public function definition()
    {
        return [
            'attendance_date' => now(),
        ];
    }
}
