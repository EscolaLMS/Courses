<?php

use EscolaLms\Courses\Enum\CoursesPermissionsEnum;

return [
    CoursesPermissionsEnum::COURSE_LIST => 'Lista kursów',
    CoursesPermissionsEnum::COURSE_CREATE => 'Utwórz kurs',
    CoursesPermissionsEnum::COURSE_UPDATE => 'Aktualizuj kurs',
    CoursesPermissionsEnum::COURSE_DELETE => 'Usuń kurs',
    CoursesPermissionsEnum::COURSE_ATTEND => 'Uczestnicz w kursie',
    CoursesPermissionsEnum::COURSE_UPDATE_OWNED => 'Aktualizuj swój kurs',
    CoursesPermissionsEnum::COURSE_DELETE_OWNED => 'Usuń swój kurs',
    CoursesPermissionsEnum::COURSE_ATTEND_OWNED => 'Uczestnicz w swoim kursie',
];