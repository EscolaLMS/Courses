<?php

namespace Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\TopicTypes\Events\VideoUpdated;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class TopicTutorUpdateApiTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('tutor');
        $this->course = Course::factory()->create([
            'author_id' => $this->user->id,
        ]);
        $this->lesson = Lesson::factory(['course_id' => $this->course->id])->create();
        $this->topic = Topic::factory()->create([
            'lesson_id' => $this->lesson->id,
            'json' => ['foo' => 'bar', 'bar' => 'foo'],
        ]);
    }

    /**
     * @test
     */
    public function testUpdateTopicImage()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->response = $this->withHeaders([
            'Content' => 'multipart/form-data',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Image',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_images', [
            'value' => $path,
        ]);
    }

    public function testUpdateTopicAudio()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'value' => $path,
        ]);
    }

    public function testUpdateTopicAudioWithNewFile()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path,
        ]);

        // ***
        // Update sending another file as value
        // ***

        $file2 = UploadedFile::fake()->create('another.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => $file2,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path,
        ]);

        // ***
        // Update sending current file path as value
        // ***

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => $path,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path,
        ]);
    }

    public function testUpdateTopicVideo()
    {
        Storage::fake('local');
        Event::fake([VideoUpdated::class]);

        $file = UploadedFile::fake()->create('avatar.mp4');

        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Video',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_videos', [
            'value' => $path,
        ]);

        Event::assertDispatched(VideoUpdated::class);
    }

    public function testUpdateTopicRichtext()
    {
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\RichText',
                'value' => 'lorem ipsum',
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path,
        ]);
    }

    public function testUpdateTopicPdf()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\PDF',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_pdfs', [
            'value' => $path,
        ]);
    }

    public function testUpdateTopicWrongClass()
    {
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\RichTextAAAAAA',
                'value' => 'lorem ipsum',
            ]
        );

        $this->response->assertStatus(422);
    }

    public function testUpdateTopicWithJson()
    {
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/'.$this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\RichText',
                'value' => 'lorem ipsum',
                'introduction' => 'asdf1',
                'summary' => 'asdf2',
                'description' => 'asdf3',
                'json' => json_encode(['foo' => 'foobar']),
            ]
        );

        $this->response->assertStatus(200);

        $data = $this->response->json();

        $this->topicId = $data['data']['id'];
        $path = $data['data']['topicable']['value'];

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path,
        ]);
        $this->assertEquals(['foo' => 'foobar'], $data['data']['json']);
        $this->assertEquals('foobar', $data['data']['json']['foo']);
        $this->assertEquals('asdf1', $data['data']['introduction']);
        $this->assertEquals('asdf2', $data['data']['summary']);
        $this->assertEquals('asdf3', $data['data']['description']);

        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->get(
            '/api/courses/'.$this->topic->lesson->course_id.'/program'
        );

        $this->response->assertOk();
        $data = $this->response->json();

        $this->assertEquals(['foo' => 'foobar'], $data['data']['lessons'][0]['topics'][0]['json']);
    }
}
