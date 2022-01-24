<?php

use EscolaLms\Courses\Enum\CoursesConstant;
use EscolaLms\Courses\Enum\PlatformVisibility;

return [
    'platform_visibility' => PlatformVisibility::VISIBILITY_PUBLIC,
    'reminder_of_deadline_count_days' => CoursesConstant::REMINDER_OF_DEADLINE_COUNT_DAYS,
    // if value === false user see all courses, if value === true user see to which has access
    'visible_only_access_courses' => CoursesConstant::VISIBLE_ONLY_ACCESS_COURSES,
];
