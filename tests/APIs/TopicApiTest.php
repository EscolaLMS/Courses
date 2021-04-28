<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Topic;

class TopicApiTest extends TestCase
{
    use /*ApiTestTrait,*/ WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */
    public function test_create_topic()
    {
        $topic = Topic::factory()->make()->toArray();

        $this->response = $this->json(
            'POST',
            '/api/topics',
            $topic
        );

        $this->assertApiResponse($topic);
    }

    /**
     * @test
     */
    public function test_read_topic()
    {
        $topic = Topic::factory()->create();

        $this->response = $this->json(
            'GET',
            '/api/topics/'.$topic->id
        );

        $this->assertApiResponse($topic->toArray());
    }

    /**
     * @test
     */
    public function test_update_topic()
    {
        $topic = Topic::factory()->create();
        $editedTopic = Topic::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/topics/'.$topic->id,
            $editedTopic
        );

        $this->assertApiResponse($editedTopic);
    }

    /**
     * @test
     */
    public function test_delete_topic()
    {
        $topic = Topic::factory()->create();

        $this->response = $this->json(
            'DELETE',
            '/api/topics/'.$topic->id
        );

        $this->assertApiSuccess();
        $this->response = $this->json(
            'GET',
            '/api/topics/'.$topic->id
        );

        $this->response->assertStatus(404);
    }
}
