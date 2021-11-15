<?php

namespace EscolaLms\Courses;

use EscolaLms\Auth\Dtos\UserUpdateDto;
use EscolaLms\Auth\Http\Requests\ProfileUpdateRequest;
use EscolaLms\Auth\Http\Resources\UserResource;
use EscolaLms\Courses\AuthServiceProvider;
use EscolaLms\Courses\Enum\PlatformVisibility;
use EscolaLms\Courses\Models\TopicContent\Audio;
use EscolaLms\Courses\Models\TopicContent\H5P;
use EscolaLms\Courses\Models\TopicContent\Image;
use EscolaLms\Courses\Models\TopicContent\OEmbed;
use EscolaLms\Courses\Models\TopicContent\PDF;
use EscolaLms\Courses\Models\TopicContent\RichText;
use EscolaLms\Courses\Models\TopicContent\Video;
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
use EscolaLms\Courses\Services\Contracts\ProgressServiceContract;
use EscolaLms\Courses\Services\CourseService;
use EscolaLms\Courses\Services\ProgressService;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use EscolaLms\Settings\Facades\AdministrableConfig;
use Illuminate\Support\ServiceProvider;

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
    ];

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
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

        $this->app->register(AuthServiceProvider::class);

        TopicRepository::registerContentClass(Audio::class);
        TopicRepository::registerContentClass(Video::class);
        TopicRepository::registerContentClass(Image::class);
        TopicRepository::registerContentClass(RichText::class);
        TopicRepository::registerContentClass(H5P::class);
        TopicRepository::registerContentClass(OEmbed::class);
        TopicRepository::registerContentClass(PDF::class);

        UserResource::extend(fn ($thisObj) => [
            'bio' => $thisObj->bio
        ]);

        UserUpdateDto::extendConstructor([
            'bio' => fn ($request) => $request->input('bio'),
        ]);

        UserUpdateDto::extendToArray([
            'bio' => fn ($thisObj) => $thisObj->bio,
        ]);

        ProfileUpdateRequest::extendRules([
            'bio' => ['string']
        ]);

        $this->callAfterResolving(EscolaLmsSettingsServiceProvider::class, function () {
            AdministrableConfig::registerConfig('escolalms_courses.platform_visibility', ['required', 'string', 'in:' . implode(',', PlatformVisibility::getValues())]);
        });
    }
}
