<?php

namespace EscolaLms\Courses\Tests\Repositories;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\CourseRepository;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CourseRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var CourseRepository
     */
    protected $courseRepo;

    public function setUp(): void
    {
        parent::setUp();
        $this->courseRepo = \App::make(CourseRepository::class);
    }

    /**
     * @test create
     */
    public function test_create_course()
    {
        $course = Course::factory()->make()->toArray();

        $createdCourse = $this->courseRepo->create($course);

        $createdCourse = $createdCourse->toArray();
        $this->assertArrayHasKey('id', $createdCourse);
        $this->assertNotNull($createdCourse['id'], 'Created Course must have id specified');
        $this->assertNotNull(Course::find($createdCourse['id']), 'Course with given id must be in DB');

        $this->assertModelData($course, $createdCourse);
    }

    /**
     * @test read
     */
    public function test_read_course()
    {
        $course = Course::factory()->create();

        $dbCourse = $this->courseRepo->find($course->id);

        $dbCourse = $dbCourse->toArray();
        $this->assertModelData($course->toArray(), $dbCourse);
    }

    /**
     * @test update
     */
    public function test_update_course()
    {
        $course = Course::factory()->create();
        $fakeCourse = Course::factory()->make()->toArray();

        $updatedCourse = $this->courseRepo->update($fakeCourse, $course->id);

        $this->assertModelData($fakeCourse, $updatedCourse->toArray());
        $dbCourse = $this->courseRepo->find($course->id);
        $this->assertModelData($fakeCourse, $dbCourse->toArray());
    }

    /**
     * @test delete
     */
    public function test_delete_course()
    {
        $course = Course::factory()->create();

        $resp = $this->courseRepo->delete($course->id);

        $this->assertTrue($resp);
        $this->assertNull(Course::find($course->id), 'Course should not exist in DB');
    }
}
