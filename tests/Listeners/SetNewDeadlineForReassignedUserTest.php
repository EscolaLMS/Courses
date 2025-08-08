<?php

namespace EscolaLms\Courses\Tests\Listeners;

use Carbon\Carbon;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Events\CourseAssigned;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\CourseUserPivot;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Contracts\Auth\Authenticatable as User;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class SetNewDeadlineForReassignedUserTest extends TestCase
{
    use DatabaseTransactions, WithFaker, CreatesUsers;

    private Carbon $now;
    private Course $course;
    private Topic $topic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = Carbon::now()->setTime(12, 0);
        Carbon::setTestNow($this->now);

        $this->course = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'hours_to_complete' => 10,
            'active_to' => null,
        ]);

        $lesson = Lesson::factory()->create(['course_id' => $this->course->getKey()]);
        $this->topic = Topic::factory()->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);
    }

    public function test_sets_deadline_when_user_has_progress(): void
    {
        $student = $this->makeStudent();
        $this->course->users()->attach($student);
        CourseProgress::create([
            'user_id' => $student->getKey(),
            'topic_id' => $this->topic->getKey(),
            'seconds' => 100,
        ]);

        event(new CourseAssigned($student, $this->course));

        $pivot = $this->getCourseUserPivot($student, $this->course);

        $this->assertNotNull($pivot->deadline);
        $this->assertEquals($this->now->copy()->addHours(10)->toDateTimeString(), $pivot->deadline->toDateTimeString());
    }

    public function test_does_not_set_deadline_without_progress(): void
    {
        $student = $this->makeStudent();
        $this->course->users()->attach($student);

        event(new CourseAssigned($student, $this->course));

        $pivot = $this->getCourseUserPivot($student, $this->course);

        $this->assertNull($pivot->deadline);
    }

    private function getCourseUserPivot(User $user, Course $course): CourseUserPivot
    {
        return CourseUserPivot::where('user_id', $user->getKey())
            ->where('course_id', $course->getKey())
            ->firstOrFail();
    }
}
