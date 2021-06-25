<?php


namespace EscolaLms\Courses\Services\Contracts;


use EscolaLms\Courses\Models\Topic;
use Illuminate\Contracts\Auth\Authenticatable;

interface ProgressServiceContract
{
    public function ping(Authenticatable $user, Topic $topic): void;
}