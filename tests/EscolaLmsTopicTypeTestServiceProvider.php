<?php

namespace EscolaLms\Courses\Tests;

use Illuminate\Support\ServiceProvider;

class EscolaLmsTopicTypeTestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
