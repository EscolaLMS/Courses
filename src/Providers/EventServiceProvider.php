<?php

namespace EscolaLms\Courses\Providers;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Observers\TopicObserver;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Topic::observe(TopicObserver::class);
    }
}
