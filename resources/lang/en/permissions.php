<?php

use EscolaLms\Courses\Enum\CoursesPermissionsEnum;

return [
    CoursesPermissionsEnum::COURSE_LIST => 'Course list',
    CoursesPermissionsEnum::COURSE_CREATE => 'Create course',
    CoursesPermissionsEnum::COURSE_UPDATE => 'Update course',
    CoursesPermissionsEnum::COURSE_DELETE => 'Delete course',
    CoursesPermissionsEnum::COURSE_ATTEND => 'Attend course',
    CoursesPermissionsEnum::COURSE_UPDATE_OWNED => 'Update owned course',
    CoursesPermissionsEnum::COURSE_DELETE_OWNED => 'Delete owned course',
    CoursesPermissionsEnum::COURSE_ATTEND_OWNED => 'Attend owned course',
];