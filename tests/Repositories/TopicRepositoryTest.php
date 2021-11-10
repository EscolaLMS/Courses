<?php

namespace EscolaLms\Courses\Tests\Repositories;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Repositories\TopicRepository;
use EscolaLms\Courses\Tests\Repositories\Mocks\CreateTopicApiRequestMock;
use EscolaLms\Courses\Tests\Repositories\Mocks\UpdateTopicApiRequestMock;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Redirector;

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
    public function testCreateTopic()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->make(['lesson_id' => $lesson->getKey()])->toArray();

        $createdTopic = $this->topicRepo->create($topic);

        $createdTopic = $createdTopic->toArray();
        $this->assertArrayHasKey('id', $createdTopic);
        $this->assertNotNull($createdTopic['id'], 'Created Topic must have id specified');
        $this->assertNotNull(Topic::find($createdTopic['id']), 'Topic with given id must be in DB');
    }

    public function testCreateTopicFromRequest()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->make(['lesson_id' => $lesson->getKey()])->toArray();

        $topic['topicable_type'] = RichText::class;
        $topic['value'] = 'lorem ipsum';

        /** @var CreateTopicApiRequestMock $request */
        $request = new CreateTopicApiRequestMock();
        $request->setContainer(app())->setRedirector(app()->make(Redirector::class));
        $request->replace($topic)->manualValidation();

        $createdTopic = $this->topicRepo->createFromRequest($request);

        $this->assertTrue($createdTopic->exists);
        $this->assertNotNull(Topic::find($createdTopic->getKey()), 'Topic with given id must be in DB');
        $this->assertEquals($topic['topicable_type'], $createdTopic->topicable_type);
        $this->assertNotNull($createdTopic->topicable->getKey());
        $this->assertEquals('lorem ipsum', $createdTopic->topicable->value);
    }

    /**
     * @test read
     */
    public function testReadTopic()
    {
        $topic = Topic::factory()->create();

        $dbTopic = $this->topicRepo->find($topic->id);

        $dbTopic = $dbTopic->toArray();
        $this->assertModelData($topic->toArray(), $dbTopic);
    }

    /**
     * @test update
     */
    public function testUpdateTopic()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->create(['lesson_id' => $lesson->getKey()]);
        $fakeTopic = Topic::factory()->make(['lesson_id' => $lesson->getKey()])->toArray();

        $updatedTopic = $this->topicRepo->update($fakeTopic, $topic->id);

        $this->assertModelData($fakeTopic, $updatedTopic->toArray());
        $dbTopic = $this->topicRepo->find($topic->id);
        $this->assertModelData($fakeTopic, $dbTopic->toArray());
    }

    public function testUpdateTopicFromRequest()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->getKey()]);
        $topic = Topic::factory()->make(['lesson_id' => $lesson->getKey()])->toArray();

        $topic['topicable_type'] = RichText::class;
        $topic['value'] = 'lorem ipsum';

        /** @var CreateTopicApiRequestMock $request */
        $request = new CreateTopicApiRequestMock();
        $request->setContainer(app())->setRedirector(app()->make(Redirector::class));
        $request->replace($topic)->manualValidation();

        $createdTopic = $this->topicRepo->createFromRequest($request);

        $this->assertTrue($createdTopic->exists);
        $this->assertNotNull(Topic::find($createdTopic->getKey()), 'Topic with given id must be in DB');
        $this->assertEquals($topic['topicable_type'], $createdTopic->topicable_type);
        $this->assertNotNull($createdTopic->topicable->getKey());
        $this->assertEquals('lorem ipsum', $createdTopic->topicable->value);

        // ***
        // Update with same topicable_type, but different value
        // ***

        $topicableKey = $createdTopic->topicable->getKey();

        $fakeTopic = Topic::factory()->make(['lesson_id' => $lesson->getKey()])->toArray();
        $fakeTopic['topicable_type'] = RichText::class;
        $fakeTopic['value'] = 'ipsum lorem';
        $fakeTopic['topic'] = $createdTopic->getKey();

        /** @var UpdateTopicApiRequestMock $request2 */
        $request2 = new UpdateTopicApiRequestMock();
        $request2->setContainer(app())->setRedirector(app()->make(Redirector::class));
        $request2->replace($fakeTopic)->manualValidation();

        $updatedTopic = $this->topicRepo->updateFromRequest($request2);

        $this->assertEquals($fakeTopic['topicable_type'], $updatedTopic->topicable_type);
        $this->assertEquals($topicableKey, $updatedTopic->topicable->getKey());
        $this->assertEquals('ipsum lorem', $updatedTopic->topicable->value);

        // ***
        // Update with different topicable type
        // ***

        $updatedTopic = $createdTopic->topicable->getKey();

        $fakeTopic2 = Topic::factory()->make(['lesson_id' => $lesson->getKey()])->toArray();
        $fakeTopic2['topicable_type'] = OEmbed::class;
        $fakeTopic2['value'] = 'https://embed.test/embed';
        $fakeTopic2['topic'] = $createdTopic->getKey();

        /** @var UpdateTopicApiRequestMock $request3 */
        $request3 = new UpdateTopicApiRequestMock();
        $request3->setContainer(app())->setRedirector(app()->make(Redirector::class));
        $request3->replace($fakeTopic2)->manualValidation();

        $updatedTopic2 = $this->topicRepo->updateFromRequest($request3);

        $this->assertEquals($fakeTopic2['topicable_type'], $updatedTopic2->topicable_type);
        $this->assertNotEquals($topicableKey, $updatedTopic2->topicable->getKey());
        $this->assertEquals('https://embed.test/embed', $updatedTopic2->topicable->value);
    }

    /**
     * @test delete
     */
    public function testDeleteTopic()
    {
        $topic = Topic::factory()->create();

        $resp = $this->topicRepo->delete($topic->id);

        $this->assertTrue($resp);
        $this->assertNull(Topic::find($topic->id), 'Topic should not exist in DB');
    }

    public function testUnregisterContentClass()
    {
        $this->topicRepo::registerContentClass(Video::class);
        $this->assertTrue(in_array(Video::class, $this->topicRepo::availableContentClasses()));
        $this->topicRepo::unregisterContentClass(Video::class);
        $this->assertTrue(!in_array(Video::class, $this->topicRepo::availableContentClasses()));
    }
}
