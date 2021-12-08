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
    public function addAccessForUsers(Course $course, array $users = []): void;
    public function addAccessForGroups(Course $course, array $groups = []): void;
    public function removeAccessForUsers(Course $course, array $users = []): void;
    public function removeAccessForGroups(Course $course, array $groups = []): void;
    public function setAccessForUsers(Course $course, array $users = []): void;
    public function setAccessForGroups(Course $course, array $groups = []): void;
}
