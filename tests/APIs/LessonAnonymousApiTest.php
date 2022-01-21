<?php

namespace EscolaLms\Courses\Tests\APIs;

use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LessonAnonymousApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_lesson()
    {
        $lesson = Lesson::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/admin/lessons',
            $lesson
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_read_lesson()
    {
        $lesson = Lesson::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/admin/lessons/' . $lesson->id
        );

        $this->response->assertStatus(401);
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
            '/api/admin/lessons/' . $lesson->id,
            $editedLesson
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_delete_lesson()
    {
        $lesson = Lesson::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/admin/lessons/' . $lesson->id
        );

        $this->response->assertStatus(401);
    }
}
