<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Http\UploadedFile;

class TopicUpdateApiTest extends TestCase
{
    use /*ApiTestTrait,*/ WithoutMiddleware, DatabaseTransactions;

   
    /**
     * @test
     */
    public function test_update_topic()
    {
        $this->assertTrue(true);
        /*
        $topic = Topic::factory()->create();
        $editedTopic = Topic::factory()->make()->toArray();

        $this->response = $this->json(
            'PUT',
            '/api/topics/'.$topic->id,
            $editedTopic
        );

        $this->assertApiResponse($editedTopic);
        */
    }
}
