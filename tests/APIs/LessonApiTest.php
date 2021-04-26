<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;
use App\Models\Lesson;

class LessonApiTest extends TestCase
{
    use ApiTestTrait, WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_lesson()
    {
        $lesson = Lesson::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/lessons', $lesson
        );

        $this->assertApiResponse($lesson);
    }

    /**
     * @test
     */
    public function test_read_lesson()
    {
        $lesson = Lesson::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/lessons/'.$lesson->id
        );

        $this->assertApiResponse($lesson->toArray());
    }

    /**
     * @test
     */
    public function test_update_lesson()
    {
        $lesson = Lesson::factory()->create();
        $editedLesson = Lesson::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/lessons/'.$lesson->id,
            $editedLesson
        );

        $this->assertApiResponse($editedLesson);
    }

    /**
     * @test
     */
    public function test_delete_lesson()
    {
        $lesson = Lesson::factory()->create();

        $this->response = $this->json(
            'DELETE',
             '/api/lessons/'.$lesson->id
         );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/lessons/'.$lesson->id
        );

        $this->response->assertStatus(404);
    }
}
