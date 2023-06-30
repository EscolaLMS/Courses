<?php

namespace EscolaLms\Courses\Tests\APIs;

use Carbon\CarbonImmutable;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Events\CourseAccessFinished;
use EscolaLms\Courses\Events\CourseAccessStarted;
use EscolaLms\Courses\Events\CourseFinished;
use EscolaLms\Courses\Events\CourseStarted;
use EscolaLms\Courses\Events\TopicFinished;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\Group;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\MakeServices;
use EscolaLms\Courses\Tests\Models\User;
use EscolaLms\Courses\Tests\ProgressConfigurable;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class CourseProgressApiTest extends TestCase
{
    use CreatesUsers, WithFaker, ProgressConfigurable, MakeServices;
    use DatabaseTransactions;

    public function test_show_progress_courses()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topics = Topic::factory(2)->create(['lesson_id' => $lesson->getKey(), 'active' => true]);
        foreach ($topics as $topic) {
            CourseProgress::create([
                'user_id' => $user->getKey(),
                'topic_id' => $topic->getKey(),
                'status' => 1
            ]);
        }
        $tag = new Tag(['title' => 'tag']);
        $course->tags()->save($tag);
        $user->courses()->save($course);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress'
        );
        $this->response->assertStatus(200);
        $this->response->assertJsonStructure([
            'data' => [
                [
                    'course',
                    'progress',
                    'categories',
                    'tags',
                    'finish_date',
                ]
            ]
        ]);
    }

    public function test_show_progress_courses_ordered_by_latest_purchased()
    {
        $user = User::factory()->create();
        $courseOne = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $courseTwo = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);

        $courseOne->users()->save($user);
        $this->travel(1)->days();
        $courseTwo->users()->save($user);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress'
        );
        $this->response->assertStatus(200);

        $this->assertEquals($courseTwo->getKey(), $this->response->json('data.0.course.id'));
        $this->assertEquals($courseOne->getKey(), $this->response->json('data.1.course.id'));
    }

    public function test_show_progress_course_from_group()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->create(['lesson_id' => $lesson->getKey(), 'active' => true]);
        $group = Group::factory()->create();
        $group->users()->attach($user);
        $group->courses()->attach($course->getKey());

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress'
        );

        $this->response->assertStatus(200);
        $this->response->assertJsonStructure([
            'data' => [
                [
                    'course',
                    'progress'
                ]
            ]
        ]);
    }

    public function test_update_course_progress(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake([TopicFinished::class, CourseAccessFinished::class, CourseFinished::class]);

        $courses = Course::factory(5)->create(['status' => CourseStatusEnum::PUBLISHED]);
        foreach ($courses as $course) {
            $lesson = Lesson::factory([
                'course_id' => $course->getKey()
            ])->create();
            $topics = Topic::factory(2)->create([
                'lesson_id' => $lesson->getKey(),
                'active' => true,
            ]);
        }
        $course = $courses->get(0);

        $student = User::factory([
            'points' => 0,
        ])->create();

        $courseProgress = CourseProgressCollection::make($student, $course);
        $this->assertFalse($courseProgress->isFinished());

        $progress = $course->topics()->first()->progress()->first();

        $this->assertTrue($progress->attempt === 0);

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course)]
        );
        $courseProgress = CourseProgressCollection::make($student, $course);
        $this->response->assertOk();
        $this->assertTrue($progress->attempt === 0);
        $this->assertTrue($courseProgress->isFinished());
        Event::assertDispatched(TopicFinished::class);
        Event::assertDispatched(CourseAccessFinished::class);
        Event::assertDispatched(CourseFinished::class);
    }

    public function test_update_course_progress_new_attempt(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake([TopicFinished::class, CourseAccessFinished::class, CourseFinished::class]);

        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory([
            'course_id' => $course->getKey()
        ])->create();
        Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);

        $student = User::factory([
            'points' => 0,
        ])->create();

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course)]
        );
        $this->response->assertOk();
        $progress = $course->topics()->first()->progress()->first();
        $this->assertTrue($progress->attempt === 0);

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course, ProgressStatus::INCOMPLETE)]
        );
        $progress = $progress->refresh();
        $this->assertTrue($progress->attempt === 1);
        Event::assertDispatched(TopicFinished::class);
        Event::assertDispatched(CourseAccessFinished::class);
        Event::assertDispatched(CourseFinished::class);
    }

    public function test_update_course_progress_incomplete(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake([TopicFinished::class, CourseAccessFinished::class, CourseFinished::class]);

        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory([
            'course_id' => $course->getKey()
        ])->create();
        Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);

        $student = User::factory([
            'points' => 0,
        ])->create();

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course)]
        );
        $this->response->assertOk();

        $progress = $course->topics()->first()->progress()->first();
        $this->assertTrue($progress->attempt === 0);

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course, ProgressStatus::INCOMPLETE)]
        )
            ->assertOk();
        $progress = $progress->refresh();
        $this->assertTrue($progress->attempt === 1);

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course, ProgressStatus::INCOMPLETE)]
        )
            ->assertOk();
        $progress = $progress->refresh();
        $this->assertTrue($progress->attempt === 1);

        Event::assertDispatched(TopicFinished::class);
        Event::assertDispatched(CourseAccessFinished::class);
        Event::assertDispatched(CourseFinished::class);
    }

    public function test_update_course_progress_not_all_incomplete(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake([TopicFinished::class, CourseAccessFinished::class, CourseFinished::class]);

        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory([
            'course_id' => $course->getKey()
        ])->create();
        Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);

        $student = User::factory([
            'points' => 0,
        ])->create();

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course)]
        );
        $this->response->assertOk();

        $updatedProgress = $this->getProgressUpdate($course, ProgressStatus::INCOMPLETE);
        $progress1 = $course->topics()->where('topics.id', '=', $updatedProgress[0]['topic_id'])->first()->progress()->first();
        $progress2 = $course->topics()->where('topics.id', '=', $updatedProgress[1]['topic_id'])->first()->progress()->first();

        $this->assertTrue($progress1->attempt === 0);
        $this->assertTrue($progress2->attempt === 0);

        $updatedProgress[0]['status'] = ProgressStatus::COMPLETE;
        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $updatedProgress]
        );
        $progress1 = $progress1->refresh();
        $progress2 = $progress2->refresh();
        $this->assertTrue($progress1->attempt === 0);
        $this->assertTrue($progress2->attempt === 0);
        Event::assertDispatched(TopicFinished::class);
        Event::assertDispatched(CourseAccessFinished::class);
        Event::assertDispatched(CourseFinished::class);
    }

    public function test_verify_course_started(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake();

        $courses = Course::factory(5)->create(['status' => CourseStatusEnum::PUBLISHED]);
        $course = $courses->get(0);

        $student = User::factory([
            'points' => 0,
        ])->create();

        $courseProgress = CourseProgressCollection::make($student, $course);

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course)]
        );

        $courseProgress = CourseProgressCollection::make($student, $course);
        $this->response->assertOk();
        $this->assertTrue($courseProgress->isFinished());
        Event::assertDispatched(CourseStarted::class);
        Event::assertDispatched(CourseAccessStarted::class);
    }

    public function test_ping_progress_course()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topics = Topic::factory(2)->create([
            'active' => true,
            'lesson_id' => $lesson->getKey(),
        ]);

        $user->courses()->sync([$course->getKey()]);

        $oneTopic = null;
        foreach ($topics as $topic) {
            $oneTopic = $topic;
            CourseProgress::create([
                'user_id' => $user->getKey(),
                'topic_id' => $topic->getKey(),
                'status' => 0
            ]);
        }

        $this->response = $this->actingAs($user, 'api')->json(
            'PUT',
            '/api/courses/progress/' . $oneTopic->getKey() . '/ping'
        );
        $this->response->assertOk();
        $this->response->assertJsonStructure([
            'data' => [
                'status'
            ]
        ]);
        $this->assertTrue($this->response->getData()->data->status);
    }

    public function test_ping_complete_topic()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topics = Topic::factory(2)->create([
            'active' => true,
            'lesson_id' => $lesson->getKey(),
        ]);

        $user->courses()->sync([$course->getKey()]);

        $oneTopic = null;
        foreach ($topics as $topic) {
            $oneTopic = $topic;
            CourseProgress::create([
                'user_id' => $user->getKey(),
                'topic_id' => $topic->getKey(),
                'status' => ProgressStatus::COMPLETE,
                'seconds' => 10,
            ]);
        }

        $this->assertDatabaseHas('course_progress', [
            'user_id' => $user->getKey(),
            'topic_id' => $oneTopic->getKey(),
            'status' => ProgressStatus::COMPLETE,
            'seconds' => 10,
        ]);
        $this->response = $this->actingAs($user, 'api')->json(
            'PUT',
            '/api/courses/progress/' . $oneTopic->getKey() . '/ping'
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'status'
                ]
            ]);
        $this->assertTrue($this->response->getData()->data->status);

        sleep(5);
        $this
            ->actingAs($user, 'api')
            ->json(
                'PUT',
                '/api/courses/progress/' . $oneTopic->getKey() . '/ping'
            )
            ->assertOk();

        $this->assertDatabaseHas('course_progress', [
            'user_id' => $user->getKey(),
            'topic_id' => $oneTopic->getKey(),
            'status' => ProgressStatus::COMPLETE,
            'seconds' => 15,
        ]);
    }

    public function test_adding_new_topic_will_reset_finished_status(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake();

        $courses = Course::factory(5)->create(['status' => CourseStatusEnum::PUBLISHED]);
        foreach ($courses as $course) {
            $lesson = Lesson::factory([
                'course_id' => $course->getKey()
            ])->create();
            $topics = Topic::factory(2)->create([
                'lesson_id' => $lesson->getKey(),
                'active' => true,
            ]);
        }
        $course = $courses->get(0);

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

        $lesson = $course->lessons->get(0);
        $topics = Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);
        $course->refresh();

        $courseProgress = CourseProgressCollection::make($student, $course);
        $this->assertFalse($courseProgress->isFinished());
    }

    public function test_active_to()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED, 'active_to' => Carbon::now()->subDay()]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey()
        ]);
        $topics = Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);
        $oneTopic = null;
        foreach ($topics as $topic) {
            $oneTopic = $topic;
            CourseProgress::create([
                'user_id' => $user->getKey(),
                'topic_id' => $topic->getKey(),
                'status' => 0
            ]);
        }
        $user->courses()->save($course);

        $this->response = $this->actingAs($user, 'api')->json(
            'PUT',
            '/api/courses/progress/' . $oneTopic->getKey() . '/ping'
        );
        $this->response->assertStatus(403);
        $this->response->assertJsonFragment([
            'message' => 'Deadline missed'
        ]);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/' . $course->getKey()
        );
        $this->response->assertStatus(200);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress'
        );
        $this->assertTrue(Carbon::parse($this->response->json('data.0.deadline'))->lessThanOrEqualTo(Carbon::now()->subDay()));
        $this->assertEquals($this->response->json('data.0.course.active_to'), $this->response->json('data.0.deadline'));
    }

    public function test_deadline()
    {
        $user = User::factory()->create();

        $now = CarbonImmutable::now()->roundSeconds();
        $hours = rand(1, 10);

        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED, 'hours_to_complete' => $hours]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey()
        ]);
        $topics = Topic::factory(2)->create([
            'lesson_id' => $lesson->getKey(),
            'active' => true,
        ]);
        $oneTopic = null;
        foreach ($topics as $topic) {
            $oneTopic = $topic;
            CourseProgress::create([
                'user_id' => $user->getKey(),
                'topic_id' => $topic->getKey(),
                'status' => 0
            ]);
        }
        $user->courses()->save($course);

        $this->response = $this->actingAs($user, 'api')->json(
            'PUT',
            '/api/courses/progress/' . $oneTopic->getKey() . '/ping'
        );
        $this->response->assertStatus(200);

        // ping two times for topic to be marked as "started"
        $this->response = $this->actingAs($user, 'api')->json(
            'PUT',
            '/api/courses/progress/' . $oneTopic->getKey() . '/ping'
        );
        $this->response->assertStatus(200);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/' . $course->getKey()
        );
        $this->response->assertStatus(200);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress'
        );
        $json = $this->response->json();
        $deadline = CarbonImmutable::parse($json['data'][0]['deadline']);

        $this->assertTrue($now->lessThan($deadline));
        $this->assertTrue($now->lessThanOrEqualTo($deadline->subHours($hours)->addSecond()));
    }

    public function test_ping_on_nonexistent_topic(): void
    {
        $course = Course::factory()->create();
        $topic = Topic::factory()
            ->state(['lesson_id' => Lesson::factory(['course_id' => $course->getKey()])])
            ->create();

        $topic->delete();

        $this->actingAs($this->makeStudent(), 'api')
            ->putJson('/api/courses/progress/' . $topic->getKey() . '/ping')
            ->assertNotFound();
    }
}
