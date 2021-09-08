<?php


namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

interface ProgressServiceContract
{
    public function ping(Authenticatable $user, Topic $topic): void;
    public function getByUser(User $user): Collection;
}
