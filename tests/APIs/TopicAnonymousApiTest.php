<?php

namespace Tests\APIs;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Topic;

class TopicAnonymousApiTest extends TestCase
{
    use /*ApiTestTrait,*/ DatabaseTransactions;

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
