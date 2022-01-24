<?php

namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Courses\Models\Topic;
use Illuminate\Database\Eloquent\Model;

interface TopicServiceContract
{
    public function cloneTopic(Topic $topic): Model;
}
