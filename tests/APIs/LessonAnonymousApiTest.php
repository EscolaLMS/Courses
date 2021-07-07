<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Lesson;

class LessonAnonymousApiTest extends TestCase
{
    use /*ApiTestTrait,*/ WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_lesson()
    {
        $lesson = Lesson::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/lessons',
            $lesson
        );

        $this->response->assertStatus(403);
    }

    /**
     * @test
     */
    public function test_read_lesson()
    {
        $lesson = Lesson::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/admin/lessons/'.$lesson->id
        );

        $this->response->assertStatus(200);
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
            '/api/admin/lessons/'.$lesson->id,
            $editedLesson
        );

        $this->response->assertStatus(403);
    }

    /**
     * @test
     */
    public function test_delete_lesson()
    {
        $lesson = Lesson::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/admin/lessons/'.$lesson->id
        );

        $this->response->assertStatus(403);
    }
}
