<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Core\Models\Config;
use EscolaLms\Core\Repositories\Contracts\ConfigRepositoryContract;
use EscolaLms\Core\Tests\CreatesUsers;
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
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

class CourseProgressApiTest extends TestCase
{
    use CreatesUsers, WithFaker, ProgressConfigurable, MakeServices;

    public function test_show_progress_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topics = Topic::factory(2)->create(['lesson_id' => $lesson->getKey()]);
        foreach ($topics as $topic) {
            CourseProgress::create([
                'user_id' => $user->getKey(),
                'topic_id' => $topic->getKey(),
                'status' => 1
            ]);
        }
        $user->courses()->save($course);

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress'
        );
        $this->response->assertStatus(200);
        $this->assertIsArray($this->response->getData());
        foreach ($this->response->getData() as $data) {
            $this->assertObjectHasAttribute('course', $data);
            $this->assertObjectHasAttribute('progress', $data);
            $this->assertNotEmpty($data->progress);
        }
    }

    public function test_show_progress_course_from_group()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->getKey()])->create();
        $topic = Topic::factory(['lesson_id' => $lesson->getKey()])->create();
        $group = Group::factory()->create();
        $group->users()->attach($user);
        $group->courses()->attach($course->getKey());

        $this->response = $this->actingAs($user, 'api')->json(
            'GET',
            '/api/courses/progress'
        );

        $this->response->assertStatus(200);
        $this->assertIsArray($this->response->getData());
        foreach ($this->response->getData() as $data) {
            $this->assertObjectHasAttribute('course', $data);
            $this->assertObjectHasAttribute('progress', $data);
            $this->assertNotEmpty($data->progress);
        }
    }

    public function test_update_course_progress(): void
    {
        Mail::fake();
        Notification::fake();
        Queue::fake();

        $courses = Course::factory(5)->create();
        foreach ($courses as $course) {
            $lesson = Lesson::factory([
                'course_id' => $course->getKey()
            ])->create();
            $topics = Topic::factory(2)->create([
                'lesson_id' => $lesson->getKey()
            ]);
        }

        $student = User::factory([
            'points' => 0,
        ])->create();

        $this->response = $this->actingAs($student, 'api')->json(
            'PATCH',
            '/api/courses/progress/' . $course->getKey(),
            ['progress' => $this->getProgressUpdate($course)]
        );
        $courseProgress = CourseProgressCollection::make($student, $course);
        $this->response->assertOk();
        $this->assertTrue($courseProgress->isFinished());
    }

    public function test_ping_progress_course()
    {
        $user = User::factory()->create();
        $courses = Course::factory(5)->create();
        $topics = Topic::factory(2)->create();
        $oneTopic = null;
        foreach ($courses as $course) {
            foreach ($topics as $topic) {
                $oneTopic = $topic;
                CourseProgress::create([
                    'user_id' => $user->getKey(),
                    'topic_id' => $topic->getKey(),
                    'status' => 0
                ]);
            }
            $user->courses()->save($course);
        }
        $this->response = $this->actingAs($user, 'api')->json(
            'PUT',
            '/api/courses/progress/' . $oneTopic->getKey() . '/ping'
        );
        $this->response->assertOk();
        $data = $this->response->getData();
        $this->assertObjectHasAttribute('status', $data);
        $this->assertTrue($data->status);
    }
}
