<?php

namespace EscolaLms\Courses;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Policies\CoursesPolicy;
use EscolaLms\Courses\Policies\LessonPolicy;
use EscolaLms\Courses\Policies\TopicPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Course::class => CoursesPolicy::class,
        Lesson::class => LessonPolicy::class,
        Topic::class => TopicPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (!$this->app->routesAreCached()) {
            Passport::routes();
        }
    }
}
