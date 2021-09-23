<?php


namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Core\Dtos\OrderDto;
use Illuminate\Database\Eloquent\Builder;

interface CourseServiceContract
{
    public function getCoursesListWithOrdering(OrderDto $orderDto, array $search = []): Builder;
    public function getScormPlayer(int $courseId);
}
