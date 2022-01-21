<?php

namespace EscolaLms\Courses\Enum;

use EscolaLms\Core\Enums\BasicEnum;

class CoursesPermissionsEnum extends BasicEnum
{
    const COURSE_LIST = 'course_list';
    const COURSE_CREATE = 'course_create';
    const COURSE_UPDATE = 'course_update';
    const COURSE_DELETE = 'course_delete';
    const COURSE_ATTEND = 'course_attend';
    const COURSE_UPDATE_OWNED = 'course_update_authored';
    const COURSE_DELETE_OWNED = 'course_delete_authored';
    const COURSE_ATTEND_OWNED = 'course_attend_authored';

    const LESSON_UPDATE = 'update';
    const LESSON_ATTEND = 'attend';

    const TOPIC_UPDATE = 'update';
    const TOPIC_ATTEND = 'attend';
}
