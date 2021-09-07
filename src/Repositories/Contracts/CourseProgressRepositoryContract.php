<?php

namespace EscolaLms\Courses\Repositories\Contracts;

use Carbon\Carbon;
use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\UserTopicTime;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Contracts\Auth\Authenticatable;

interface CourseProgressRepositoryContract extends BaseRepositoryContract
{
    public function findProgress(Topic $topic, Authenticatable $user): ?CourseProgress;

    public function getFieldsSearchable();

    public function model();

    public function updateInTopic(Topic $topic, Authenticatable $user, int $status, ?int $seconds = null): void;

    public function getUserLastTimeInTopic(Authenticatable $user, Topic $topic, int $forgetAfter = CourseProgressCollection::FORGET_TRACKING_SESSION_AFTER_MINUTES): ?Carbon;

    public function updateUserTimeInTopic(Authenticatable $user, Topic $topic): void;

    public function getUserTimeInTopic(Authenticatable $user, Topic $topic, int $forgetAfter = CourseProgressCollection::FORGET_TRACKING_SESSION_AFTER_MINUTES): ?UserTopicTime;
}
