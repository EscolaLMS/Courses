<?php

use EscolaLms\Courses\Enum\CoursesConstant;
use EscolaLms\Courses\Enum\CourseVisibilityEnum;
use EscolaLms\Courses\Enum\PlatformVisibility;

return [
    'platform_visibility' => PlatformVisibility::VISIBILITY_PUBLIC,
    'reminder_of_deadline_count_days' => CoursesConstant::REMINDER_OF_DEADLINE_COUNT_DAYS,
    'course_visibility' => CourseVisibilityEnum::SHOW_ALL,
    'tutor_bio_field' => 'bio',
];
