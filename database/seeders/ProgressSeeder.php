<?php

namespace EscolaLms\Courses\Database\Seeders;

use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\CourseProgressRepository;
use EscolaLms\Courses\Services\ProgressService;
use EscolaLms\Courses\Tests\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ProgressSeeder extends Seeder
{
    public function run()
    {
        /** @var Collection $students */
        $students = User::role(UserRole::STUDENT)->whereHas('courses')->take(10)->get();
        if ($students->isEmpty()) {
            $students = User::role(UserRole::STUDENT)->take(10)->get();
            foreach ($students as $student) {
                $student->courses()->save(Course::inRandomOrder()->first());
            }
        }
        $progressService = app(ProgressService::class);
        $progressRepository = app(CourseProgressRepository::class);
        foreach ($students as $student) {
            $progressedCourses = $progressService->getByUser($student);
            foreach ($progressedCourses as $course) {
                /** @var Course $course */
                foreach ($course->topic as $topic) {
                    $status = ProgressStatus::getRandomValue();
                    $progressRepository->updateInTopic($topic, $student, $status, $status !== ProgressStatus::INCOMPLETE ? rand(60, 300) : null);
                    if ($status === ProgressStatus::IN_PROGRESS) {
                        $progressService->ping($student, $topic);
                    }
                }
                $progressService->update($course, $student, []);
            }
        }
    }
}
