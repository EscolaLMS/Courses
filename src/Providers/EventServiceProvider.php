<?php

namespace EscolaLms\Courses\Providers;

use EscolaLms\Courses\Events\CourseAssigned;
use EscolaLms\Courses\Listeners\SetNewDeadlineForReassignedUser;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\ResponseCache\Events\ClearedResponseCache;
use Spatie\ResponseCache\Facades\ResponseCache;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CourseAssigned::class => [
            SetNewDeadlineForReassignedUser::class,
        ],
    ];

    public function boot(): void
    {
        Event::listen([
            'eloquent.created: EscolaLms*',
            'eloquent.updated: EscolaLms*',
            'eloquent.deleted: EscolaLms*',
        ], function() {
            ResponseCache::clear();
            event(ClearedResponseCache::class);
        });
    }
}
