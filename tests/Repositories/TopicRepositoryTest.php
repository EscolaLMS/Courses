<?php namespace Tests\Repositories;

use App\Models\Topic;
use App\Repositories\TopicRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\ApiTestTrait;

class TopicRepositoryTest extends TestCase
{
    use ApiTestTrait, DatabaseTransactions;

    /**
     * @var TopicRepository
     */
    protected $topicRepo;

    public function setUp() : void
    {
        parent::setUp();
        $this->topicRepo = \App::make(TopicRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_topic()
    {
        $topic = Topic::factory()->make()->toArray();

        $createdTopic = $this->topicRepo->create($topic);

        $createdTopic = $createdTopic->toArray();
        $this->assertArrayHasKey('id', $createdTopic);
        $this->assertNotNull($createdTopic['id'], 'Created Topic must have id specified');
        $this->assertNotNull(Topic::find($createdTopic['id']), 'Topic with given id must be in DB');
        $this->assertModelData($topic, $createdTopic);
    }

    /**
     * @test read
     */
    public function test_read_topic()
    {
        $topic = Topic::factory()->create();

        $dbTopic = $this->topicRepo->find($topic->id);

        $dbTopic = $dbTopic->toArray();
        $this->assertModelData($topic->toArray(), $dbTopic);
    }

    /**
     * @test update
     */
    public function test_update_topic()
    {
        $topic = Topic::factory()->create();
        $fakeTopic = Topic::factory()->make()->toArray();

        $updatedTopic = $this->topicRepo->update($fakeTopic, $topic->id);

        $this->assertModelData($fakeTopic, $updatedTopic->toArray());
        $dbTopic = $this->topicRepo->find($topic->id);
        $this->assertModelData($fakeTopic, $dbTopic->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_topic()
    {
        $topic = Topic::factory()->create();

        $resp = $this->topicRepo->delete($topic->id);

        $this->assertTrue($resp);
        $this->assertNull(Topic::find($topic->id), 'Topic should not exist in DB');
    }
}
