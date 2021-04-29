<?php


namespace EscolaLms\Courses\Services\Contracts;


use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Dto\CourseSearchDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CourseServiceContract
{
    public function searchInCategory(CourseSearchDto $courseSearchDto, Category $category): LengthAwarePaginator;

    public function searchInCategoryAndSubCategory(Category $category): LengthAwarePaginator;
}