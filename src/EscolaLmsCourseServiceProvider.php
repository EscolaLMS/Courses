<?php

namespace EscolaLms\Courses;

use EscolaLms\Courses\Providers\SettingsServiceProvider;
use EscolaLms\Courses\Repositories\Contracts\CourseH5PProgressRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\CourseProgressRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\LessonRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Courses\Repositories\Contracts\TopicResourceRepositoryContract;
use EscolaLms\Courses\Repositories\CourseH5PProgressRepository;
use EscolaLms\Courses\Repositories\CourseProgressRepository;
use EscolaLms\Courses\Repositories\CourseRepository;
use EscolaLms\Courses\Repositories\LessonRepository;
use EscolaLms\Courses\Repositories\TopicRepository;
use EscolaLms\Courses\Repositories\TopicResourceRepository;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Courses\Services\Contracts\LessonServiceContract;
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\Services\Contracts\TopicServiceContract;
use EscolaLms\Courses\Services\CourseService;
use EscolaLms\Courses\Services\LessonService;
use EscolaLms\Courses\Services\ProgressService;
use EscolaLms\Courses\Services\TopicService;
use Illuminate\Support\ServiceProvider;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\ResponseCacheServiceProvider;

class EscolaLmsCourseServiceProvider extends ServiceProvider
{
    public $singletons = [
        CourseH5PProgressRepositoryContract::class => CourseH5PProgressRepository::class,
        CourseProgressRepositoryContract::class => CourseProgressRepository::class,
        CourseRepositoryContract::class => CourseRepository::class,
        CourseServiceContract::class => CourseService::class,
        ProgressServiceContract::class => ProgressService::class,
        TopicRepositoryContract::class => TopicRepository::class,
        TopicResourceRepositoryContract::class => TopicResourceRepository::class,
        LessonRepositoryContract::class => LessonRepository::class,
        TopicServiceContract::class => TopicService::class,
        LessonServiceContract::class => LessonService::class,
    ];

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        $router = $this->app->get('router');
        $router->aliasMiddleware('cacheResponse', CacheResponse::class);
    }

    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path('escolalms_courses.php'),
        ], 'escolalms_courses.config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'escolalms_courses');
        $this->mergeConfigFrom(__DIR__ . '/../config/responsecache.php', 'responsecache');

        $this->app->register(AuthServiceProvider::class);
        $this->app->register(ScheduleServiceProvider::class);
        $this->app->register(SettingsServiceProvider::class);
        $this->app->register(ResponseCacheServiceProvider::class);
    }
}
