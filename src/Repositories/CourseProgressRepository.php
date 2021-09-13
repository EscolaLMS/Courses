<?php

namespace EscolaLms\Courses\Repositories;

use Carbon\Carbon;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\UserTopicTime;
use EscolaLms\Courses\Repositories\Contracts\CourseProgressRepositoryContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Contracts\Auth\Authenticatable;

class CourseProgressRepository extends BaseRepository implements CourseProgressRepositoryContract
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_id',
        'user_id',
        'status',
        'finished_at',
        'active'
    ];

    public function findProgress(Topic $topic, Authenticatable $user): ?CourseProgress
    {
        return $this->model->where('topic_id', $topic->getKey())->where('user_id', $user->getKey())->first();
    }

    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    public function model()
    {
        return CourseProgress::class;
    }

    public function updateInTopic(Topic $topic, Authenticatable $user, int $status, ?int $seconds = null): void
    {
        $update = ['status' => $status];

        if (!is_null($seconds)) {
            $update['seconds'] = $seconds;
        }

        if ($status == ProgressStatus::COMPLETE) {
            $update['finished_at'] = Carbon::now();
        }

        $topic->progress()->updateOrCreate([
            'user_id' => $user->getKey(),
        ], $update);
    }

    public function getUserLastTimeInTopic(Authenticatable $user, Topic $topic, int $forgetAfter = CourseProgressCollection::FORGET_TRACKING_SESSION_AFTER_MINUTES): ?Carbon
    {
        return $this->getUserTimeInTopic($user, $topic, $forgetAfter)->updated_at ?? null;
    }

    public function getUserTimeInTopic(Authenticatable $user, Topic $topic, int $forgetAfter = CourseProgressCollection::FORGET_TRACKING_SESSION_AFTER_MINUTES): ?UserTopicTime
    {
        $criteria = ['user_id' => $user->getKey(), 'topic_id' => $topic->getKey()];

        return UserTopicTime::where($criteria)
            ->where('updated_at', '>=', Carbon::now()->subMinutes($forgetAfter))
            ->select('updated_at')
            ->first();
    }

    public function updateUserTimeInTopic(Authenticatable $user, Topic $topic): void
    {
        UserTopicTime::where(['user_id' => $user->getKey(), 'topic_id' => $topic->getKey()])->delete();
        UserTopicTime::create(['user_id' => $user->getKey(), 'topic_id' => $topic->getKey()]);
    }

    public function updateUserTimeInApp(Authenticatable $user): void
    {
        UserTopicTime::where(['user_id' => $user->getKey()])->whereNull('topic_id')->delete();
        UserTopicTime::create(['user_id' => $user->getKey(), 'topic_id' => null]);
    }
}
