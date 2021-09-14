<?php

namespace EscolaLms\Courses\Database\Seeders;

use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\User;
use EscolaLms\Courses\Repositories\CourseProgressRepository;
use EscolaLms\Courses\Services\ProgressService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ProgressSeeder extends Seeder
{
    public function run()
    {
        if (Course::count() === 0) {
            $this->call(CoursesSeeder::class);
        }

        /** @var Collection $students */
        $students = User::role(UserRole::STUDENT)->whereHas('courses')->take(10)->get();
        if ($students->isEmpty()) {
            $students = User::role(UserRole::STUDENT)->take(10)->get();
            if ($students->isEmpty()) {
                $students = User::factory()->count(10)->create();
                foreach ($students as $student) {
                    $student->assignRole(UserRole::STUDENT);
                }
            }
            foreach ($students as $student) {
                /** @var User $student */
                $student->courses()->syncWithoutDetaching([Course::inRandomOrder()->first()->getKey()]);
            }
        }

        /** @var ProgressService $progressService */
        $progressService = app(ProgressService::class);
        /** @var CourseProgressRepository $progressRepository */
        $progressRepository = app(CourseProgressRepository::class);
        foreach ($students as $student) {
            /** @var User $student */
            $progressedCourses = $progressService->getByUser($student);
            foreach ($progressedCourses as $course) {
                /** @var Course $course */
                foreach ($course->topics as $topic) {
                    /** @var Topic $topic */
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
