<?php


namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Dtos\PaginationDto;
use Illuminate\Database\Eloquent\Builder;

interface CourseServiceContract
{
    public function getCoursesListWithOrdering(OrderDto $orderDto, PaginationDto $paginationDto, array $search = []): Builder;
    public function getScormPlayer(int $courseId);
}
