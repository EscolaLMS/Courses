<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Events\TopicFinished;
use EscolaLms\Courses\Jobs\CheckFinishedLessons;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\CourseUserAttendance;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\UserTopicTime;
use EscolaLms\Courses\Repositories\Contracts\CourseProgressRepositoryContract;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;

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

    public function updateInTopic(Topic $topic, Authenticatable $user, int $status, ?int $seconds = null, ?bool $newAttempt = false): void
    {
        $update = ['status' => $status];

        if (!is_null($seconds)) {
            $update['seconds'] = $seconds;
        }

        $progress = $this->findProgress($topic, $user);
        if ($status === ProgressStatus::COMPLETE && $progress && $progress->status !== ProgressStatus::COMPLETE) {
            $update['finished_at'] = Carbon::now();
            event(new TopicFinished($user, $topic));
            CheckFinishedLessons::dispatch($topic->getKey(), $user->getKey());
        }

        $courseProgress = $topic->progress()->updateOrCreate([
            'user_id' => $user->getKey(),
        ], $update);

        if ($newAttempt && $status === ProgressStatus::INCOMPLETE && !$courseProgress->wasRecentlyCreated && $courseProgress->wasChanged()) {
            $courseProgress->increment('attempt');
        }

        if (is_null($courseProgress->started_at)) {
            if ($courseProgress->finished_at) {
                $courseProgress->started_at = $courseProgress->finished_at->subSeconds($courseProgress->seconds ?? 0);
            } elseif (!is_null($courseProgress->seconds)) {
                $courseProgress->started_at = Carbon::now()->subSeconds($courseProgress->seconds);
            }
            $courseProgress->save();
        }
        CourseUserAttendance::updateOrCreate([
            'course_progress_id' => $courseProgress->getKey(),
            'attendance_date' => $courseProgress->updated_at,
            'attempt' => $courseProgress->attempt ?? 0,
        ], [
            'seconds' => $courseProgress->seconds ?? 0,
        ]);
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
