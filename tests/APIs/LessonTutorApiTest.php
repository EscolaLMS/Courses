<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\Models\TopicContent\ExampleTopicType;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;

class LessonTutorApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
    }

    /**
     * @test
     */
    public function test_create_lesson()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->make(['course_id' => $course->id])->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'POST',
            '/api/admin/lessons',
            $lesson
        );

        $this->assertApiResponse($lesson);
    }

    /**
     * @test
     */
    public function test_read_lesson()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey()
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/lessons/' . $lesson->id
        );

        $this->assertApiResponse($lesson->toArray());
    }

    /**
     * @test
     */
    public function test_update_lesson()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $editedLesson = Lesson::factory()->make()->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json(
            'PUT',
            '/api/admin/lessons/' . $lesson->id,
            $editedLesson
        );

        $this->assertApiResponse($editedLesson);
    }

    /**
     * @test
     */
    public function test_delete_lesson()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->id
        ]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'DELETE',
            '/api/admin/lessons/' . $lesson->id
        );

        $this->assertApiSuccess();
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/lessons/' . $lesson->id
        );

        $this->response->assertStatus(404);
    }

    public function test_clone_lesson_not_found()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey(),
        ]);
        $lesson->delete();

        $this->response = $this->actingAs($this->user, 'api')
            ->postJson('/api/admin/lessons/' . $lesson->getKey() . '/clone');

        $this->response->assertStatus(404);
    }

    public function test_clone_lesson()
    {
        $course = Course::factory()->create([
            'author_id' => $this->user->getKey(),
        ]);
        $lesson = Lesson::factory()->create([
            'course_id' => $course->getKey(),
        ]);
        $topicable = ExampleTopicType::factory()->create();
        Topic::factory()->create([
            'lesson_id' => $lesson->getKey(),
            'topicable_type' => ExampleTopicType::class,
            'topicable_id' => $topicable->getKey(),
        ]);

        $exceptedOrder = 1 + (int) $course->lessons->max('order');

        $this->response = $this->actingAs($this->user, 'api')
            ->postJson('/api/admin/lessons/' . $lesson->getKey() . '/clone');

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());
        $clonedLessonId = $data->data->id;
        $this->assertApiResponse(array_diff_key($lesson->toArray(), array_flip(['id', 'course_id', 'order'])));

        $this->assertDatabaseHas('lessons', [
            'id' => $clonedLessonId,
            'course_id' => $course->getKey(),
        ]);

        $this->assertDatabaseHas('topics', [
            'lesson_id' => $clonedLessonId,
        ]);

        $this->assertEquals($exceptedOrder, $data->data->order);
    }
}
