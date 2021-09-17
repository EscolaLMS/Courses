<?php

namespace Tests\Repositories;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\TopicRepository;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TopicRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var TopicRepository
     */
    protected $topicRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->topicRepo = \App::make(TopicRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_topic()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->make(['lesson_id' => $lesson->getKey()])->toArray();
        $topic['topicable_type'] = "EscolaLms\Courses\Models\TopicContent\RichText";
        $topic['value'] = "lorem ipsum";

        $createdTopic = $this->topicRepo->create($topic);

        $createdTopic = $createdTopic->toArray();
        $this->assertArrayHasKey('id', $createdTopic);
        $this->assertNotNull($createdTopic['id'], 'Created Topic must have id specified');
        $this->assertNotNull(Topic::find($createdTopic['id']), 'Topic with given id must be in DB');
        //$this->assertModelData($topic, $createdTopic);
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
