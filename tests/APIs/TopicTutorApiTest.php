<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\Models\TopicContent\ExampleTopicType;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TopicTutorApiTest extends TestCase
{
    use /*ApiTestTrait,*/ DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
    }

    public function testReadTopic()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id,
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id,
            'json' => ['foo' => 'bar', 'bar' => 'foo'],
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/topics/'.$topic->id
        );

        $this->assertApiResponse($topic->toArray());

        $this->response->assertJsonPath('data.json.foo', 'bar');
        $this->response->assertJsonPath('data.json.bar', 'foo');
    }

    /**
     * @test
     */
    public function testDeleteTopic()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id,
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/admin/topics/'.$topic->id
        );

        $this->assertApiSuccess();
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/topics/'.$topic->id
        );

        $this->response->assertStatus(404);
    }

    public function testDeleteTopicByDeletingWholeCourse()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id,
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/admin/courses/'.$course->id
        );

        $this->assertApiSuccess();
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/topics/'.$topic->id
        );

        $this->response->assertStatus(404);
    }

    public function testDeleteRestrictTopic()
    {
        $user2 = config('auth.providers.users.model')::factory()->create();
        $user2->guard_name = 'api';
        $user2->assignRole('tutor');

        $course = Course::factory()->create([
            'author_id' => $user2->id,
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id,
        ]);
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->id,
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/admin/topics/'.$topic->id
        );

        $this->response->assertStatus(403);
    }

    public function testCloneTopic(): void
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey(),
        ]);
        $topicable = ExampleTopicType::factory()->create();
        $topic = Topic::factory()->create([
            'lesson_id' => $lesson->getKey(),
            'topicable_type' => ExampleTopicType::class,
            'topicable_id' => $topicable->getKey(),
        ]);

        $this->response = $this->actingAs($this->user, 'api')
            ->postJson('/api/admin/topics/' . $topic->getKey() . '/clone');

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $value = $data->data->topicable->value;

        $this->assertDatabaseHas('topics', [
            'id' => $topicId,
            'topicable_type' => ExampleTopicType::class,
            'topicable_id' => $data->data->topicable->id,
        ]);

        $this->assertDatabaseHas('topic_example', [
            'value' => $value,
        ]);
    }
}
