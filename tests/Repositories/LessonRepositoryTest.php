<?php

namespace EscolaLms\Courses\Tests\Repositories;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Repositories\LessonRepository;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LessonRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var LessonRepository
     */
    protected $lessonRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lessonRepo = \App::make(LessonRepository::class);
        Course::factory()->create(); // Lesson factory requires at least one Course existing in DB
    }

    /**
     * @test create
     */
    public function test_create_lesson()
    {
        $lesson = Lesson::factory()->make()->toArray();

        $createdLesson = $this->lessonRepo->create($lesson);

        $createdLesson = $createdLesson->toArray();
        $this->assertArrayHasKey('id', $createdLesson);
        $this->assertNotNull($createdLesson['id'], 'Created Lesson must have id specified');
        $this->assertNotNull(Lesson::find($createdLesson['id']), 'Lesson with given id must be in DB');
        $this->assertModelData($lesson, $createdLesson);
    }

    /**
     * @test read
     */
    public function test_read_lesson()
    {
        $lesson = Lesson::factory()->create();

        $dbLesson = $this->lessonRepo->find($lesson->id);

        $dbLesson = $dbLesson->toArray();
        $this->assertModelData($lesson->toArray(), $dbLesson);
    }

    /**
     * @test update
     */
    public function test_update_lesson()
    {
        $lesson = Lesson::factory()->create();
        $fakeLesson = Lesson::factory()->make()->toArray();

        $updatedLesson = $this->lessonRepo->update($fakeLesson, $lesson->id);

        $this->assertModelData($fakeLesson, $updatedLesson->toArray());
        $dbLesson = $this->lessonRepo->find($lesson->id);
        $this->assertModelData($fakeLesson, $dbLesson->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_lesson()
    {
        $lesson = Lesson::factory()->create();

        $resp = $this->lessonRepo->delete($lesson->id);

        $this->assertTrue($resp);
        $this->assertNull(Lesson::find($lesson->id), 'Lesson should not exist in DB');
    }
}
