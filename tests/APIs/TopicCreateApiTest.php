<?php namespace Tests\APIs;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use EscolaLms\Courses\Tests\TestCase;
//use Tests\ApiTestTrait;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TopicCreateApiTest extends TestCase
{
    use /*ApiTestTrait,*/ WithoutMiddleware, DatabaseTransactions;

    /**
     * @test
     */

    public function test_create_topic_image()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();

        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->response = $this->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $lesson->id,
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

    public function test_create_topic_audio()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();

        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.mp3');

        $this->response = $this->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $lesson->id,
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

    public function test_create_topic_video()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();

        Storage::fake('local');

        $file = UploadedFile::fake()->image('avatar.mp4');

        $this->response = $this->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $lesson->id,
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

    public function test_create_topic_richtext()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();

        $this->response = $this->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $lesson->id,
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

    public function test_create_topic_no_lesson()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();

        $this->response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\RichText',
                'value' => 'lorem ipsum'
            ]
        );

        $this->response->assertStatus(422);
    }

    /////

    public function test_create_topic_image_no_file()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();


        $this->response = $this->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $lesson->id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Image',
                'value' => 'file'
            ]
        );

        $this->response->assertStatus(422);
    }

    public function test_create_topic_audio_no_file()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();


        $this->response = $this->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $lesson->id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Audio',
                'value' => 'file'
            ]
        );

        $this->response->assertStatus(422);
    }

    public function test_create_topic_video_no_file()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();

        $this->response = $this->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $lesson->id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\Video',
                'value' => 'file'
            ]
        );

        $this->response->assertStatus(422);
    }

    public function test_create_topic_wrong_class()
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory(['course_id' => $course->id])->create();

        $this->response = $this->post(
            '/api/topics',
            [
                'title' => 'Hello World',
                'lesson_id' => $lesson->id,
                'topicable_type' => 'EscolaLms\Courses\Models\TopicContent\RichTextAAAAAA',
                'value' => 'lorem ipsum'
            ]
        );

        $this->response->assertStatus(422);
    }
}
