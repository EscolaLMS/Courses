<?php

namespace EscolaLms\Courses\Enum;

use EscolaLms\Core\Enums\BasicEnum;

class ProgressFilterEnum extends BasicEnum
{
    const PLANNED = 'planned';
    const STARTED = 'started';
    const FINISHED = 'finished';
}
