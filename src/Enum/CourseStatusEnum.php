<?php

namespace EscolaLms\Courses\Enum;

use EscolaLms\Core\Enums\BasicEnum;

class CourseStatusEnum extends BasicEnum
{
    const DRAFT     = 'draft';
    const PUBLISHED = 'published';
    const ARCHIVED  = 'archived';
}
