<?php


namespace EscolaLms\Courses\Repositories\Contracts;


use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Builder;

interface CourseRepositoryContract
{

    public function allQueryBuilder(array $search = [], ?int $skip = null, ?int $limit = null, array $criteria = []): Builder;

    public function queryAll(): Builder;
}