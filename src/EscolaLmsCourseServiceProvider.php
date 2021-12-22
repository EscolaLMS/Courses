<?php

namespace EscolaLms\Courses;

use EscolaLms\Auth\Dtos\UserUpdateDto;
use EscolaLms\Auth\Http\Requests\ProfileUpdateRequest;
use EscolaLms\Auth\Http\Resources\UserResource;
use EscolaLms\Courses\AuthServiceProvider;
use EscolaLms\Courses\Enum\PlatformVisibility;
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
use EscolaLms\TopicTypes\Models\TopicContent\Audio;
use EscolaLms\TopicTypes\Models\TopicContent\H5P;
use EscolaLms\TopicTypes\Models\TopicContent\Image;
use EscolaLms\TopicTypes\Models\TopicContent\OEmbed;
use EscolaLms\TopicTypes\Models\TopicContent\PDF;
use EscolaLms\TopicTypes\Models\TopicContent\RichText;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        Relation::morphMap([
            'EscolaLms\Courses\Models\TopicContent\Audio' => Audio::class,
            'EscolaLms\Courses\Models\TopicContent\H5P' => H5P::class,
            'EscolaLms\Courses\Models\TopicContent\Image' => Image::class,
            'EscolaLms\Courses\Models\TopicContent\OEmbed' => OEmbed::class,
            'EscolaLms\Courses\Models\TopicContent\PDF' => PDF::class,
            'EscolaLms\Courses\Models\TopicContent\RichText' => RichText::class,
            'EscolaLms\Courses\Models\TopicContent\Video' => Video::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        AdministrableConfig::registerConfig('escolalms_courses.platform_visibility', ['required', 'string', 'in:' . implode(',', PlatformVisibility::getValues())]);
        AdministrableConfig::registerConfig('escolalms_courses.reminder_of_deadline_count_days', ['integer', 'min: 1']);
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

        if (!app()->bound(EscolaLmsSettingsServiceProvider::class)) {
            $this->app->register(EscolaLmsSettingsServiceProvider::class);
        }

        $this->app->register(AuthServiceProvider::class);
        $this->app->register(ScheduleServiceProvider::class);

        UserResource::extend(fn ($thisObj) => [
            'bio' => $thisObj->bio,
        ]);

        UserUpdateDto::extendConstructor([
            'bio' => fn ($request) => $request->input('bio'),
        ]);

        UserUpdateDto::extendToArray([
            'bio' => fn ($thisObj) => $thisObj->bio,
        ]);

        ProfileUpdateRequest::extendRules([
            'bio' => ['string'],
        ]);
    }
}
