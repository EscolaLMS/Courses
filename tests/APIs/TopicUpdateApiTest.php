<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\UploadedFile;

class TopicUpdateApiTest extends TestCase
{
    use /*ApiTestTrait,*/ WithoutMiddleware, DatabaseTransactions;

   
    /**
    * @test
    */

    public function test_update_topic_image()
    {
        $topic = Topic::factory()->create();
        
        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->response = $this->withHeaders([
            'Content' => 'multipart/form-data',
            'Accept' => 'application/json',
        ])->post(
            '/api/topics/'.$topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Image',
                'value' => $file
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/".$path);

        $this->assertDatabaseHas('topic_images', [
            'value' => $path
        ]);
    }

    

    public function test_update_topic_audio()
    {
        $topic = Topic::factory()->create();


        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.mp3');

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/topics/'.$topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Audio',
                'value' => $file
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/".$path);

        $this->assertDatabaseHas('topic_audios', [
            'value' => $path
        ]);
    }

    public function test_update_topic_video()
    {
        $topic = Topic::factory()->create();

        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.mp4');

        $this->response = $this->withHeaders([
'Content' => 'application/x-www-form-urlencoded',
'Accept' => 'application/json',
        ])->post(
            '/api/topics/'.$topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Video',
                'value' => $file
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        Storage::disk('local')->assertExists("/".$path);

        $this->assertDatabaseHas('topic_videos', [
            'value' => $path
        ]);
    }

    public function test_update_topic_richtext()
    {
        $topic = Topic::factory()->create();

        $this->response = $this->withHeaders([
'Content' => 'application/x-www-form-urlencoded',
'Accept' => 'application/json',
        ])->post(
            '/api/topics/'.$topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\RichText',
                'value' => 'lorem ipsum'
            ]
        );

        $this->response->assertStatus(200);

        $data = json_decode($this->response->getContent());

        $topicId = $data->data->id;
        $path = $data->data->topicable->value;

        $this->assertDatabaseHas('topic_richtexts', [
            'value' => $path
        ]);
    }

   

    public function test_update_topic_wrong_class()
    {
        $topic = Topic::factory()->create();

        $this->response = $this->withHeaders([
'Content' => 'application/x-www-form-urlencoded',
'Accept' => 'application/json',
        ])->post(
            '/api/topics/'.$topic->id,
            [
                'title' => 'Hello World',
                'lesson_id' => $topic->lesson_id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\RichTextAAAAAA',
                'value' => 'lorem ipsum'
            ]
        );

        $this->response->assertStatus(422);
    }
}
