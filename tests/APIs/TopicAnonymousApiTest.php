<?php

namespace Tests\APIs;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TopicAnonymousApiTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create([
            'course_id' => $course->id
        ]);
        $this->topic = Topic::factory()->create([
            'lesson_id' => $lesson->id
        ]);
    }

    /**
     * @test
     */
    public function test_read_topic()
    {
        $this->response = $this->json(
            'GET',
            '/api/admin/topics/' . $this->topic->id
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_delete_topic()
    {
        $this->response = $this->json(
            'DELETE',
            '/api/admin/topics/' . $this->topic->id
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_update_topic()
    {
        $this->response = $this->json(
            'POST',
            '/api/admin/topics/' . $this->topic->id
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_read_topic_types()
    {
        $this->response = $this->json(
            'GET',
            '/api/admin/topics/types'
        );

        $this->response->assertStatus(401);
    }
}
