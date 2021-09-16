<?php

namespace Tests\APIs;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TopicAnonymousApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function test_read_topic()
    {
        $topic = Topic::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/admin/topics/' . $topic->id
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_delete_topic()
    {
        $topic = Topic::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/admin/topics/' . $topic->id
        );


        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_update_topic()
    {
        $topic = Topic::factory()->create();

        $this->response = $this->json(
            'POST',
            '/api/admin/topics/' . $topic->id
        );

        $this->response->assertStatus(401);
    }

    /**
     * @test
     */
    public function test_read_topic_types()
    {
        $topic = Topic::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/admin/topics/types'
        );

        $this->response->assertStatus(401);
    }
}
