<?php

namespace EscolaLms\Courses;

use EscolaLms\Core\Providers\Injectable;
use Illuminate\Support\ServiceProvider;

class EscolaLmsCourseServiceProvider extends ServiceProvider
{
    use Injectable;

    private const CONTRACTS = [
    ];

    public function register()
    {
        $this->injectContract(self::CONTRACTS);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->app->register(EventServiceProvider::class);
    }
}
