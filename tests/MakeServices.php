<?php

namespace EscolaLms\Courses\Tests;

use EscolaLms\Categories\Services\Contracts\CategoryServiceContracts;
use EscolaLms\Core\Repositories\Contracts\ConfigRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\CourseProgressRepositoryContract;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;

trait MakeServices
{
    public function courseService(): CourseServiceContract
    {
        return $this->courseService = app(CourseServiceContract::class);
    }

    public function courseProgressRepository(): CourseProgressRepositoryContract
    {
        return $this->courseProgressRepository = app(CourseProgressRepositoryContract::class);
    }

    public function categoryService(): CategoryServiceContracts
    {
        return $this->courseService = app(CategoryServiceContracts::class);
    }

    public function configRepository(): ConfigRepositoryContract
    {
        return $this->configRepository = app(ConfigRepositoryContract::class);
    }

}