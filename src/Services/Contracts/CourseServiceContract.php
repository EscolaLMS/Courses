<?php


namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Courses\Dto\CourseSearchDto;
use EscolaLms\Courses\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CourseServiceContract
{

    public function attachCategories(Course $course, array $categories);

    public function attachTags(Course $course, array $tags);
}
