<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Tests\Models\TopicContent\ExampleTopicType;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;

class TopicTutorCreateApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
        $this->course = Course::factory()->create([
            'author_id' => $this->user->id,
        ]);
        $this->lesson = Lesson::factory(['course_id' => $this->course->id])->create();
    }

    public function testCreateTopicExample()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => ExampleTopicType::class,
                'value' => 'lorem ipsum',
            ]
        );
        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        $this->assertDatabaseHas('topic_example', [
            'value' => $path,
        ]);
    }

}
