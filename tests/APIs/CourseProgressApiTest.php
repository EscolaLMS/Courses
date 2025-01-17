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
use EscolaLms\Courses\Jobs\CheckFinishedLessons;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\CourseProgress;
use EscolaLms\Courses\Models\CourseUserPivot;
use EscolaLms\Courses\Models\Group;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\UserTopicTime;
use EscolaLms\Courses\Tests\MakeServices;
use EscolaLms\Courses\Tests\Models\User;
use EscolaLms\Courses\Tests\ProgressConfigurable;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class CourseProgressApiTest extends TestCase
{
    use CreatesUsers, WithFaker, ProgressConfigurable, MakeServices;
    use DatabaseTransactions;

    public function test_show_progress_courses(): void
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

    public function test_show_progress_courses_paginated_ordered(): void
    {
        $user = User::factory()->create();

        $course1 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'A Course',
        ]);
        $lesson1 = Lesson::factory()->create(['course_id' => $course1->getKey()]);
        Topic::factory()->create(['lesson_id' => $lesson1->getKey(), 'active' => true]);

        $course2 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'B Course',
        ]);
        $lesson2 = Lesson::factory()->create(['course_id' => $course2->getKey()]);
        Topic::factory()->create(['lesson_id' => $lesson2->getKey(), 'active' => true]);

        $course3 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'C Course',
        ]);
        $lesson3 = Lesson::factory()->create(['course_id' => $course3->getKey()]);
        Topic::factory()->create(['lesson_id' => $lesson3->getKey(), 'active' => true]);

        $course4 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'D Course',
        ]);
        $lesson4 = Lesson::factory()->create(['course_id' => $course4->getKey()]);
        Topic::factory()->create(['lesson_id' => $lesson4->getKey(), 'active' => true]);

        $user->courses()->save($course4); //Course D
        CourseUserPivot::query()
            ->where('user_id', $user->getKey())
            ->where('course_id', $course4->getKey())
            ->update(['created_at' => null]);

        $user->courses()->save($course2); //Course B

        $this->travel(1)->days();

        $user->courses()->save($course1); //Course A
        /** @var Group $group */
        $group = Group::factory()->create();

        $this->travel(1)->days();

        $course3->groups()->save($group); //Course C
        $course2->groups()->save($group); //Course B
        $user->groups()->save($group);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/paginated',
        );

        $this->assertTrue($this->response->json('data.0.course.id') === $course3->getKey());
        $this->assertTrue($this->response->json('data.1.course.id') === $course1->getKey());
        $this->assertTrue($this->response->json('data.2.course.id') === $course2->getKey());
        $this->assertTrue($this->response->json('data.3.course.id') === $course4->getKey());

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
            ],
            'meta' => [
                'per_page',
                'total',
            ]
        ]);

        $this->response->assertJsonFragment([
            'per_page' => 20,
            'total' => 4,
        ]);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/paginated',
            [
                'order' => 'asc',
            ]
        );

        $this->assertTrue($this->response->json('data.0.course.id') === $course4->getKey());
        $this->assertTrue($this->response->json('data.1.course.id') === $course2->getKey());
        $this->assertTrue($this->response->json('data.2.course.id') === $course1->getKey());
        $this->assertTrue($this->response->json('data.3.course.id') === $course3->getKey());

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/paginated',
            [
                'order_by' => 'title',
                'order' => 'asc',
            ],
        );

        $this->assertTrue($this->response->json('data.0.course.id') === $course1->getKey());
        $this->assertTrue($this->response->json('data.1.course.id') === $course2->getKey());
        $this->assertTrue($this->response->json('data.2.course.id') === $course3->getKey());
        $this->assertTrue($this->response->json('data.3.course.id') === $course4->getKey());

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/paginated',
            [
                'order_by' => 'title',
                'order' => 'desc',
            ],
        );

        $this->assertTrue($this->response->json('data.0.course.id') === $course4->getKey());
        $this->assertTrue($this->response->json('data.1.course.id') === $course3->getKey());
        $this->assertTrue($this->response->json('data.2.course.id') === $course2->getKey());
        $this->assertTrue($this->response->json('data.3.course.id') === $course1->getKey());
    }

    public function test_show_progress_courses_paginated_filtered_planned(): void
    {
        $user = User::factory()->create();

        $course1 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'A Course',
        ]);
        $lesson1 = Lesson::factory()->create(['course_id' => $course1->getKey()]);
        $topic1 = Topic::factory()->create(['lesson_id' => $lesson1->getKey(), 'active' => true]);

        $course2 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'B Course',
        ]);
        $lesson2 = Lesson::factory()->create(['course_id' => $course2->getKey()]);
        $topic2 = Topic::factory()->create(['lesson_id' => $lesson2->getKey(), 'active' => true]);

        $course3 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'C Course',
        ]);
        $lesson3 = Lesson::factory()->create(['course_id' => $course3->getKey()]);
        $topic3 = Topic::factory()->create(['lesson_id' => $lesson3->getKey(), 'active' => true]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic1->getKey(),
            'finished_at' => null,
            'seconds' => null,
            'status' => ProgressStatus::INCOMPLETE
        ]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic2->getKey(),
            'finished_at' => null,
            'started_at' => now(),
            'seconds' => 1000,
            'status' => ProgressStatus::IN_PROGRESS,
        ]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic3->getKey(),
            'finished_at' => now(),
            'started_at' => now(),
            'seconds' => 0,
            'status' => ProgressStatus::COMPLETE,
        ]);

        $user->courses()->save($course1); //Course A
        $user->courses()->save($course2); //Course B
        $user->courses()->save($course3); //Course C

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/paginated', [
                'status' => 'planned',
            ]
        );

        $this->response
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title' => $course1->title,
            ]);
    }

    public function test_show_progress_courses_paginated_filtered_finished(): void
    {
        $user = User::factory()->create();

        // finished
        $course1 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'A Course',
        ]);
        $lesson1 = Lesson::factory()->create(['course_id' => $course1->getKey(), 'active' => true]);
        $topic1 = Topic::factory()->create(['lesson_id' => $lesson1->getKey(), 'active' => true]);
        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic1->getKey(),
            'finished_at' => now(),
        ]);

        $user->courses()->save($course1);

        // not finished
        $course2 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'B Course',
        ]);
        $lesson2 = Lesson::factory()->create(['course_id' => $course2->getKey(), 'active' => true]);
        $topic2 = Topic::factory()->create(['lesson_id' => $lesson2->getKey(), 'active' => true]);
        $topic3 = Topic::factory()->create(['lesson_id' => $lesson2->getKey(), 'active' => true]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic2->getKey(),
            'finished_at' => now(),
        ]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic3->getKey(),
            'finished_at' => null,
        ]);

        $user->courses()->save($course2);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/paginated', [
                'status' => 'finished',
            ]
        );

        $this->response
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'title' => $course1->title,
            ]);
    }

    public function test_show_progress_courses_paginated_filtered_started(): void
    {
        $user = User::factory()->create();

        $course1 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'A Course',
        ]);
        $lesson1 = Lesson::factory()->create(['course_id' => $course1->getKey()]);
        $topic1 = Topic::factory()->create(['lesson_id' => $lesson1->getKey(), 'active' => true]);
        $topic2 = Topic::factory()->create(['lesson_id' => $lesson1->getKey(), 'active' => true]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic1->getKey(),
            'finished_at' => now(),
            'seconds' => 60,
            'status' => ProgressStatus::COMPLETE,
        ]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic2->getKey(),
            'finished_at' => null,
            'seconds' => 10,
            'status' => ProgressStatus::IN_PROGRESS,
        ]);

        $course2 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'B Course',
        ]);
        $lesson2 = Lesson::factory()->create(['course_id' => $course2->getKey()]);
        $topic3 = Topic::factory()->create(['lesson_id' => $lesson2->getKey(), 'active' => true]);
        $topic4 = Topic::factory()->create(['lesson_id' => $lesson2->getKey(), 'active' => true]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic3->getKey(),
            'finished_at' => now(),
            'seconds' => 50,
            'status' => ProgressStatus::COMPLETE,
        ]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic4->getKey(),
            'finished_at' => now(),
            'seconds' => 50,
            'status' => ProgressStatus::COMPLETE,
        ]);

        $course3 = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'title' => 'B Course',
        ]);
        $lesson3 = Lesson::factory()->create(['course_id' => $course2->getKey()]);
        $topic4 = Topic::factory()->create(['lesson_id' => $lesson3->getKey(), 'active' => true]);
        $topic5 = Topic::factory()->create(['lesson_id' => $lesson3->getKey(), 'active' => true]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic4->getKey(),
            'finished_at' => now(),
            'seconds' => 50,
            'status' => ProgressStatus::COMPLETE,
        ]);

        CourseProgress::factory()->create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic5->getKey(),
            'finished_at' => now(),
            'seconds' => 50,
            'status' => ProgressStatus::INCOMPLETE,
        ]);


        $user->courses()->save($course1); //Course A
        $user->courses()->save($course2); //Course B
        $user->courses()->save($course3); //Course C

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress/paginated', [
                'status' => 'started',
            ]
        );

        $this->response
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'title' => $course1->title,
            ])
            ->assertJsonFragment([
                'title' => $course3->title,
            ]);
    }

    public function test_show_progress_courses_ordered_by_latest_purchased(): void
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

    public function test_show_progress_course_from_group(): void
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

    public function test_show_progress_course_from_parent_group(): void
    {
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->create(['lesson_id' => $lesson->getKey(), 'active' => true]);
        $parentGroup = Group::factory()->create();
        $parentGroup->courses()->attach($course->getKey());
        $group = Group::factory()->create(['parent_id' => $parentGroup->getKey()]);
        $user = User::factory()->create();
        $group->users()->attach($user);

        $this->actingAs($user, 'api')
            ->getJson('/api/courses/progress')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'course',
                    'progress',
                ]]
            ]);
    }

    public function test_show_progress_courses_with_end_date(): void
    {
        $student1 = $this->makeStudent();
        $student2 = $this->makeStudent();
        $endDate = Carbon::now()->startOfDay()->subDay();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);

        $student1->courses()->attach($course->getKey(), ['end_date' => $endDate]);
        $student2->courses()->attach($course->getKey());

        $this->actingAs($student1, 'api')
            ->getJson('/api/courses/progress')
            ->assertStatus(200)
            ->assertJsonFragment([
                'end_date' => $endDate
            ])
            ->assertJsonStructure([
                'data' => [[
                    'course',
                    'progress',
                    'categories',
                    'tags',
                    'finish_date',
                    'end_date'
                ]]
            ]);

        $this->actingAs($student2, 'api')
            ->getJson('/api/courses/progress')
            ->assertStatus(200)
            ->assertJsonFragment([
                'end_date' => null
            ])
            ->assertJsonStructure([
                'data' => [[
                    'course',
                    'progress',
                    'categories',
                    'tags',
                    'finish_date',
                    'end_date'
                ]]
            ]);
    }

    public function test_update_course_progress(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake([TopicFinished::class, CourseAccessFinished::class, CourseFinished::class]);
        Bus::fake([CheckFinishedLessons::class]);

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
        Bus::assertDispatched(CheckFinishedLessons::class);
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

    public function test_ping_progress_course(): void
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

    public function test_ping_should_not_dispatch_topic_finished_event_again(): void
    {
        Event::fake([TopicFinished::class]);
        /** @var User $user */
        $user = User::factory()->create();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->create([
            'active' => true,
            'lesson_id' => $lesson->getKey(),
        ]);

        $user->courses()->sync([$course->getKey()]);
        UserTopicTime::create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic->getKey(),
        ]);
        CourseProgress::create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic->getKey(),
            'status' => ProgressStatus::COMPLETE,
        ]);

        $this->response = $this->actingAs($user, 'api')
            ->putJson('/api/courses/progress/' . $topic->getKey() . '/ping')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'status',
                ],
            ]);

        Event::assertNotDispatched(TopicFinished::class);
    }

    public function test_ping_complete_topic(): void
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

    public function test_ping_complete_topic_when_end_date_is_overdue(): void
    {
        $user = $this->makeStudent();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->create([
            'active' => true,
            'lesson_id' => $lesson->getKey(),
        ]);

        $user->courses()->attach($course->getKey(), ['end_date' => Carbon::now()->subDay()]);

        CourseProgress::create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic->getKey(),
            'status' => ProgressStatus::COMPLETE,
            'seconds' => 10,
        ]);

        $this->actingAs($user, 'api')
            ->putJson('/api/courses/progress/' . $topic->getKey() . '/ping')
            ->assertOk()
            ->assertJsonFragment([
                'status' => true
            ]);

        sleep(5);

        $this->actingAs($user, 'api')
            ->putJson('/api/courses/progress/' . $topic->getKey() . '/ping')
            ->assertOk()
            ->assertJsonFragment([
                'status' => true
            ]);

        $this->assertDatabaseHas('course_progress', [
            'user_id' => $user->getKey(),
            'topic_id' => $topic->getKey(),
            'status' => ProgressStatus::COMPLETE,
            'seconds' => 10,
        ]);
    }

    public function test_ping_complete_topic_when_end_date_is_current(): void
    {
        $user = $this->makeStudent();
        $course = Course::factory()->create(['status' => CourseStatusEnum::PUBLISHED]);
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->create([
            'active' => true,
            'lesson_id' => $lesson->getKey(),
        ]);

        $user->courses()->syncWithPivotValues($course->getKey(), ['end_date' => Carbon::now()->addDay()]);

        CourseProgress::create([
            'user_id' => $user->getKey(),
            'topic_id' => $topic->getKey(),
            'status' => ProgressStatus::COMPLETE,
            'seconds' => 10,
        ]);

        $this->actingAs($user, 'api')
            ->putJson('/api/courses/progress/' . $topic->getKey() . '/ping')
            ->assertOk()
            ->assertJsonFragment([
                'status' => true
            ]);

        sleep(5);

        $this->actingAs($user, 'api')
            ->putJson('/api/courses/progress/' . $topic->getKey() . '/ping')
            ->assertOk()
            ->assertJsonFragment([
                'status' => true
            ]);

        $this->assertDatabaseHas('course_progress', [
            'user_id' => $user->getKey(),
            'topic_id' => $topic->getKey(),
            'status' => ProgressStatus::COMPLETE,
            'seconds' => 15,
        ]);
    }

    public function test_adding_new_topic_will_reset_finished_status(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();
        Event::fake([CourseAccessFinished::class]);

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

    public function test_active_to(): void
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

    public function test_deadline(): void
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

    public function test_deadline_after_updated_course_active_to(): void
    {
        $activeFrom = Carbon::now();
        $activeTo = Carbon::now()->addDay();

        $user = User::factory()->create();
        $course = Course::factory()->create([
            'status' => CourseStatusEnum::PUBLISHED,
            'active_from' => $activeFrom,
            'active_to' => $activeTo,
        ]);

        $course->users()->attach($user);

        $this->response = $this->actingAs($user, 'api')
            ->getJson('/api/courses/progress/')
            ->assertStatus(200);

        $deadline = CarbonImmutable::parse($this->response->json('data.0.deadline'));
        $this->assertEquals($activeTo->format('Y-m-d H:i'), $deadline->format('Y-m-d H:i'));

        $activeTo = Carbon::now()->addDays(3);

        $this->response = $this->actingAs($this->makeAdmin(), 'api')
            ->postJson('/api/admin/courses/' . $course->getKey(), [
                'active_to' => $activeTo,
            ])
            ->assertStatus(200);

        $this->response = $this->actingAs($user, 'api')
            ->getJson('/api/courses/progress/')
            ->assertStatus(200);

        $deadline = CarbonImmutable::parse($this->response->json('data.0.deadline'));
        $this->assertEquals($activeTo->format('Y-m-d H:i'), $deadline->format('Y-m-d H:i'));
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
