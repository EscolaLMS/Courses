<?php

namespace EscolaLms\Courses\Tests;

use EscolaLms\Core\Models\User as ModelsUser;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Database\Seeders\NotificationsSeeder;
use EscolaLms\Courses\Events\CourseCompleted;
use EscolaLms\Courses\Events\DeadlineIncoming;
use EscolaLms\Courses\Jobs\CheckForDeadlines;
use EscolaLms\Courses\Listeners\CourseCompletedListener;
use EscolaLms\Courses\Listeners\DeadlineIncomingListener;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\User;
use EscolaLms\Courses\Notifications\DeadlineNotification;
use EscolaLms\Courses\Notifications\UserAssignedToCourseNotification;
use EscolaLms\Courses\Notifications\UserFinishedCourseNotification;
use EscolaLms\Courses\Notifications\UserUnassignedFromCourseNotification;
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
        $this->seed(NotificationsSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
    }

    public function testDeadlineNotification()
    {
        Notification::fake();
        Event::fake();

        $user = User::factory()->create();
        $course = Course::factory()->create(['active' => true, 'active_to' => Carbon::now()->addHour()]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey()
        ]);
        $topics = Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);
        $user->courses()->save($course);
        $progress = CourseProgressCollection::make($user, $course);

        $checkForDealines = new CheckForDeadlines();
        $checkForDealines->handle();

        Event::assertDispatched(DeadlineIncoming::class, function (DeadlineIncoming $event) use ($user, $course) {
            return $event->getCourse()->getKey() === $course->getKey() && $event->getUser()->getKey() === $user->getKey();
        });

        $event = new DeadlineIncoming($user, $course);
        $listener = new DeadlineIncomingListener();
        $listener->handle($event);

        Notification::assertSentTo($user, DeadlineNotification::class, function (DeadlineNotification $notification) use ($user, $course) {
            return $notification->additionalDataForVariables($user)[0]->getKey() === $course->getKey();
        });
    }

    public function testUserAssignedToCourseNotification()
    {
        Notification::fake();

        $course = Course::factory()->create([
            'author_id' => $this->user->id,
            'base_price' => 1337,
            'active' => true
        ]);

        $student = User::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $course->id . '/access/add/', [
            'users' => [$student->getKey()]
        ]);

        $this->response->assertOk();

        $user = ModelsUser::find($student->getKey());
        Notification::assertSentTo($user, UserAssignedToCourseNotification::class);
    }

    public function testUserUnassignedFromCourseNotification()
    {
        Notification::fake();

        $course = Course::factory()->create([
            'author_id' => $this->user->id,
            'base_price' => 1337,
            'active' => true
        ]);
        $student = User::factory()->create();
        $student->courses()->save($course);

        $this->response = $this->actingAs($this->user, 'api')->post('/api/admin/courses/' . $course->id . '/access/remove/', [
            'users' => [$student->getKey()]
        ]);

        $this->response->assertOk();

        $user = ModelsUser::find($student->getKey());
        Notification::assertSentTo($user, UserUnassignedFromCourseNotification::class);
    }

    public function testUserFinishedCourseNotification()
    {
        Notification::fake();
        Event::fake();

        $course = Course::factory()->create(['active' => true]);
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

        Event::assertDispatched(CourseCompleted::class);

        $event = new CourseCompleted($course, $student);
        $listener = new CourseCompletedListener();
        $listener->handle($event);

        Notification::assertSentTo($student, UserFinishedCourseNotification::class);
    }
}
