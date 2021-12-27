<?php

namespace EscolaLms\Courses\Tests\APIs;

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
            'course_id' => $course->getKey()
        ]);
        $this->topic = Topic::factory()->create([
            'lesson_id' => $lesson->getKey()
        ]);
    }

    /**
     * @test
     */
    public function testReadTopic()
    {
        $this->response = $this->json(
            'GET',
            '/api/admin/topics/' . $this->topic->getKey()
        );

        $this->response->assertStatus(401);
    }
}
