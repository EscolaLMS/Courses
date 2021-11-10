<?php

namespace Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\TopicTypes\Events\VideoUpdated;
use EscolaLms\TopicTypes\Models\TopicContent\Audio;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class TopicTutorCreateApiTest extends TestCase
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
    }

    /**
     * @test
     */
    public function testCreateTopicImage()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Image::class,
                'value' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_images', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicAudio()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => Audio::class,
                'value' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_audios', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicPdf()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\PDF',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_pdfs', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicVideo()
    {
        Storage::fake('local');
        Event::fake([VideoUpdated::class]);

        $file = UploadedFile::fake()->image('avatar.mp4');

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Video',
                'value' => $file,
            ]
        );

        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists('/'.$path);

        $this->assertDatabaseHas('topic_videos', [
            'value' => $path,
        ]);

        Event::assertDispatched(VideoUpdated::class);
    }

    public function testCreateTopicRichtext()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => RichText::class,
                'value' => 'lorem ipsum',
            ]
        );
        $this->response->assertStatus(201);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path,
        ]);
    }

    public function testCreateTopicNoLesson()
    {
        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\RichText',
                'value' => 'lorem ipsum',
            ]
        );

        $this->response->assertStatus(401);
    }

    public function testCreateTopicImageNoFile()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Image',
                'value' => 'file',
            ]
        );

        $this->response->assertStatus(422);
    }

    public function testCreateTopicAudioNoFile()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Audio',
                'value' => 'file',
            ]
        );

        $this->response->assertStatus(422);
    }

    public function testCreateTopicVideoNoFile()
    {
        $course = Course::factory()->create();

        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\TopicTypes\Models\TopicContent\Video',
                'value' => 'file',
            ]
        );

        $this->response->assertStatus(422);
    }

    public function testCreateTopicWrongClass()
    {
        $this->response = $this->actingAs($this->user, 'api')->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/admin/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $this->lesson->id,
                'topicable_type' => 'EscolaLms\Courses\TopicTypes\TopicContent\RichTextAAAAAA',
                'value' => 'lorem ipsum',
            ],
        );

        $this->response->assertStatus(422);
    }
}
