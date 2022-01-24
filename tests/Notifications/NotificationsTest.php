<?php

namespace EscolaLms\Courses\Tests\Notifications;

use EscolaLms\Core\Models\User as ModelsUser;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Events\CourseAccessStarted;
use EscolaLms\Courses\Events\CourseAssigned;
use EscolaLms\Courses\Events\CourseAccessFinished;
use EscolaLms\Courses\Events\CourseStarted;
use EscolaLms\Courses\Events\CourseUnassigned;
use EscolaLms\Courses\Events\CourseDeadlineSoon;
use EscolaLms\Courses\Jobs\CheckForDeadlines;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\User;
use EscolaLms\Courses\Tests\ProgressConfigurable;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

class NotificationsTest extends TestCase
{
    use DatabaseTransactions;
    use ProgressConfigurable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
    }

    public function testDeadlineNotification()
    {
        Notification::fake();
        Event::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED, 'active_to' => Carbon::now()->addDays(config('escolalms_courses.reminder_of_deadline_count_days'))]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey()
        ]);
        Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);
        $user->courses()->save($course);
        CourseProgressCollection::make($user, $course);

        $checkForDealines = new CheckForDeadlines();
        $checkForDealines->handle();

        Event::assertDispatched(CourseDeadlineSoon::class, function (CourseDeadlineSoon $event) use ($user, $course) {
            return $event->getCourse()->getKey() === $course->getKey() && $event->getUser()->getKey() === $user->getKey();
        });
    }

    public function testDeadlineNotificationNotDispach()
    {
        Notification::fake();
        Event::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED, 'active_to' => Carbon::now()->addHour()]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey()
        ]);
        Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);

        $user->courses()->save($course);
        CourseProgressCollection::make($user, $course);
        $checkForDealines = new CheckForDeadlines();
        $checkForDealines->handle();

        Event::assertNotDispatched(CourseDeadlineSoon::class, function (CourseDeadlineSoon $event) use ($user, $course) {
            return $event->getCourse()->getKey() === $course->getKey() && $event->getUser()->getKey() === $user->getKey();
        });
    }

    public function testUserAssignedToCourseNotification()
    {
        Notification::fake();
        Event::fake([CourseAccessStarted::class, CourseAssigned::class]);

        $course = Course::factory()->create([
            'author_id' => $this->user->id,
            'base_price' => 1337,
            'status' => CourseStatusEnum::PUBLISHED,
        ]);

        $student = User::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $course->id . '/access/add/', [
            'users' => [$student->getKey()]
        ]);

        $this->response->assertOk();
        Event::assertDispatched(CourseAccessStarted::class);

        $user = ModelsUser::find($student->getKey());
        Event::assertDispatched(CourseAssigned::class, function (CourseAssigned $event) use ($user, $course) {
            return $event->getCourse()->getKey() === $course->getKey() && $event->getUser()->getKey() === $user->getKey();
        });
    }

    public function testUserUnassignedFromCourseNotification()
    {
        Notification::fake();
        Event::fake(CourseUnassigned::class);

        $course = Course::factory()->create([
            'author_id' => $this->user->id,
            'base_price' => 1337,
            'status' => CourseStatusEnum::PUBLISHED
        ]);
        $student = User::factory()->create();
        $student->courses()->save($course);

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $course->id . '/access/remove/', [
            'users' => [$student->getKey()]
        ]);

        $this->response->assertOk();

        $user = ModelsUser::find($student->getKey());
        Event::assertDispatched(CourseUnassigned::class, function (CourseUnassigned $event) use ($user, $course) {
            return $event->getCourse()->getKey() === $course->getKey() && $event->getUser()->getKey() === $user->getKey();
        });
    }

    public function testUserFinishedCourseNotification()
    {
        Notification::fake();
        Event::fake();

        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory([
            'course_id' => $course->getKey()
        ])->create();
        $topics = Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);

        $student = User::factory([
            'points' => 0,
        ])->create();

        $courseProgress = CourseProgressCollection::make($student, $course);
        $this->assertFalse($courseProgress->isFinished());

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course)]
        );
        $courseProgress = CourseProgressCollection::make($student, $course);
        $this->response->assertOk();
        $this->assertTrue($courseProgress->isFinished());

        Event::assertDispatched(CourseAccessFinished::class);

        $user = ModelsUser::find($student->getKey());
        Event::assertDispatched(CourseAccessFinished::class, function (CourseAccessFinished $event) use ($user, $course) {
            return $event->getCourse()->getKey() === $course->getKey() && $event->getUser()->getKey() === $user->getKey();
        });
    }
}
