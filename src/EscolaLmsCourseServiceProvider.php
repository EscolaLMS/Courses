<?php

namespace EscolaLms\Courses;

use EscolaLms\Courses\Repositories\Contracts\CourseProgressRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Courses\Repositories\CourseProgressRepository;
use EscolaLms\Courses\Repositories\CourseRepository;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\Services\CourseService;
use EscolaLms\Courses\Services\ProgressService;
use Illuminate\Support\ServiceProvider;
use EscolaLms\Courses\Models\TopicContent\Audio;
use EscolaLms\Courses\Models\TopicContent\Video;
use EscolaLms\Courses\Models\TopicContent\Image;
use EscolaLms\Courses\Models\TopicContent\RichText;
use EscolaLms\Courses\Models\TopicContent\H5P;
use EscolaLms\Courses\Repositories\TopicRepository;

class EscolaLmsCourseServiceProvider extends ServiceProvider
{
    public $singletons = [
        CourseServiceContract::class => CourseService::class,
        ProgressServiceContract::class => ProgressService::class,
        CourseRepositoryContract::class => CourseRepository::class,
        CourseProgressRepositoryContract::class => CourseProgressRepository::class,
        TopicRepositoryContract::class => TopicRepository::class
    ];

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function register()
    {
        TopicRepository::registerContentClass(Audio::class);
        TopicRepository::registerContentClass(Video::class);
        TopicRepository::registerContentClass(Image::class);
        TopicRepository::registerContentClass(RichText::class);
        TopicRepository::registerContentClass(H5P::class);
    }
}
