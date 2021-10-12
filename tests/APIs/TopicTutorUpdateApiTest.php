<?php

namespace Tests\APIs;

use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
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
            'author_id' => $this->user->id
        ]);
        $this->lesson = Lesson::factory(['course_id' => $this->course->id])->create();
        $this->topic = Topic::factory()->create([
            'lesson_id' => $this->lesson->id,
            'json' => ['foo' => 'bar', 'bar' => 'foo']
        ]);
    }

    /**
     * @test
     */

    public function test_update_topic_image()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->response = $this->withHeaders([
            'Content' => 'multipart/form-data',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Image',
                'value' => $file
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/" . $path);

        $this->assertDatabaseHas('topic_images', [
            'value' => $path
        ]);
    }



    public function test_update_topic_audio()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Audio',
                'value' => $file
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/" . $path);

        $this->assertDatabaseHas('topic_audios', [
            'value' => $path
        ]);
    }

    public function test_update_topic_audio_with_new_file()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('avatar.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Audio',
                'value' => $file
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/" . $path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path
        ]);

        // ***
        // Update sending another file as value
        // ***

        $file2 = UploadedFile::fake()->create('another.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Audio',
                'value' => $file2
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/" . $path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path
        ]);

        // ***
        // Update sending current file path as value
        // ***

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Audio',
                'value' => $path
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/" . $path);

        $this->assertDatabaseHas('topic_audios', [
            'id' => $data->data->topicable->id,
            'value' => $path
        ]);
    }

    public function test_update_topic_video()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('avatar.mp4');

        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Video',
                'value' => $file
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/" . $path);

        $this->assertDatabaseHas('topic_videos', [
            'value' => $path
        ]);
    }

    public function test_update_topic_richtext()
    {
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\RichText',
                'value' => 'lorem ipsum'
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path
        ]);
    }

    public function test_update_topic_pdf()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf');

        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\PDF',
                'value' => $file
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $this->topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/" . $path);

        $this->assertDatabaseHas('topic_pdfs', [
            'value' => $path
        ]);
    }

    public function test_update_topic_wrong_class()
    {
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\RichTextAAAAAA',
                'value' => 'lorem ipsum'
            ]
        );

        $this->response->assertStatus(422);
    }

    public function test_update_topic_with_json()
    {
        $this->response = $this->withHeaders([
            'Content' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
        ])->actingAs($this->user, 'api')->post(
            '/api/admin/topics/' . $this->topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $this->topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\RichText',
                'value' => 'lorem ipsum',
                'json' => json_encode(['foo' => 'foobar'])
            ]
        );

        $this->response->assertStatus(200);

        $data = $this->response->json();

        $this->topicId = $data['data']['id'];
        $path = $data['data']['topicable']['value'];

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path
        ]);
        $this->assertEquals(['foo' => 'foobar'], $data['data']['json']);
        $this->assertEquals('foobar', $data['data']['json']['foo']);
    }
}
