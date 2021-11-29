<?php


namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Eloquent\Builder;

interface CourseServiceContract
{
    public function getCoursesListWithOrdering(OrderDto $orderDto, array $search = []): Builder;
    public function getScormPlayer(int $courseId);
    public function sort($class, $orders): void;
    public function sendNotificationsForCourseAssignments(Course $course, array $changes): void;
}
