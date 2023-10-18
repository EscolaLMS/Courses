<?php

namespace EscolaLms\Courses\Tests\Jobs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Events\LessonFinished;
use EscolaLms\Courses\Jobs\CheckFinishedLessons;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

class CheckFinishedLessonsTest extends TestCase
{
    use DatabaseTransactions, WithFaker, CreatesUsers;

    public function testShouldNotDispatchLessonFinishedWhenTopicNonExists(): void
    {
        Event::fake([LessonFinished::class]);

        $user = $this->makeStudent();
        $topic = Topic::factory()->for(Lesson::factory()->for(Course::factory()))->create();
        $topic->delete();
        CheckFinishedLessons::dispatch($topic->getKey(), $user->getKey());
        Event::assertNotDispatched(LessonFinished::class);
    }

    public function testShouldNotDispatchLessonFinishedWhenNotAllTopicsInLessonAreFinished(): void
    {
        Event::fake([LessonFinished::class]);

        $user = $this->makeStudent();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->for($course)->create();
        Topic::factory()->state(['active' => true])->for($lesson)->create();
        $finishedTopic = Topic::factory()->state(['active' => true])->for($lesson)->create();

        CourseProgress::factory()->state([
            'user_id' => $user->getKey(),
            'topic_id' => $finishedTopic->getKey(),
            'status' => ProgressStatus::COMPLETE,
            'finished_at' => Carbon::now(),
        ])
            ->create();

        CheckFinishedLessons::dispatch($finishedTopic->getKey(), $user->getKey());
        Event::assertNotDispatched(LessonFinished::class);
    }

    public function testShouldDispatchLessonFinishedWhenAllTopicsInLessonAreFinished(): void
    {
        Event::fake([LessonFinished::class]);

        $user = $this->makeStudent();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->for($course)->create();
        $topics = Topic::factory()->count(3)->state(['active' => true])->for($lesson)->create();

        foreach ($topics as $topic) {
            CourseProgress::factory()->state([
                'user_id' => $user->getKey(),
                'topic_id' => $topic->getKey(),
                'status' => ProgressStatus::COMPLETE,
                'finished_at' => Carbon::now(),
            ])
                ->create();
        }

        CheckFinishedLessons::dispatch($topics->first()->getKey(), $user->getKey());
        Event::assertDispatched(function (LessonFinished $lessonFinished) use ($lesson, $user) {
            $this->assertEquals($lesson->getKey(), $lessonFinished->getLesson()->getKey());
            $this->assertEquals($user->getKey(), $lessonFinished->getUser()->getKey());
            return true;
        });
    }

    public function testShouldDispatchLessonFinishedEventForLessonAndParentLesson(): void
    {
        Event::fake([LessonFinished::class]);

        $user = $this->makeStudent();
        $course = Course::factory()->create();
        $parentLesson = Lesson::factory()->state(['active' => true])->for($course)->create();
        $parentLessonTopics = Topic::factory()->count(3)->state(['active' => true])->for($parentLesson)->create();

        $childLesson = Lesson::factory()->for($parentLesson, 'parentLesson')->for($course)->create();
        $childTopic = Topic::factory()->state(['active' => true])->for($childLesson)->create();

        foreach ($parentLessonTopics->push($childTopic) as $topic) {
            CourseProgress::factory()->state([
                'user_id' => $user->getKey(),
                'topic_id' => $topic->getKey(),
                'status' => ProgressStatus::COMPLETE,
                'finished_at' => Carbon::now(),
            ])
                ->create();
        }

        CheckFinishedLessons::dispatch($childTopic->getKey(), $user->getKey());

        Event::assertDispatchedTimes(LessonFinished::class, 2);
        Event::assertDispatched(LessonFinished::class, function (LessonFinished $lessonFinished) use ($childLesson, $user, $parentLesson) {
            $this->assertContains($lessonFinished->getLesson()->getKey(), [$childLesson->getKey(), $parentLesson->getKey()]);
            $this->assertEquals($user->getKey(), $lessonFinished->getUser()->getKey());
            return true;
        });
    }

    public function testShouldDispatchLessonFinishedEventOnlyForChildLesson(): void
    {
        Event::fake([LessonFinished::class]);

        $user = $this->makeStudent();
        $course = Course::factory()->create();
        $parentLesson = Lesson::factory()->state(['active' => true])->for($course)->create();
        Topic::factory()->count(3)->state(['active' => true])->for($parentLesson)->create();

        $childLesson = Lesson::factory()->for($parentLesson, 'parentLesson')->for($course)->create();
        $childTopic = Topic::factory()->state(['active' => true])->for($childLesson)->create();

        CourseProgress::factory()->state([
            'user_id' => $user->getKey(),
            'topic_id' => $childTopic->getKey(),
            'status' => ProgressStatus::COMPLETE,
            'finished_at' => Carbon::now(),
        ])
            ->create();

        CheckFinishedLessons::dispatch($childTopic->getKey(), $user->getKey());

        Event::assertDispatchedTimes(LessonFinished::class);
        Event::assertDispatched(function (LessonFinished $lessonFinished) use ($childLesson, $user) {
            $this->assertEquals($childLesson->getKey(), $lessonFinished->getLesson()->getKey());
            $this->assertEquals($user->getKey(), $lessonFinished->getUser()->getKey());
            return true;
        });
    }
}
