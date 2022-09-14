<?php

namespace EscolaLms\Courses\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\ResponseCache\Events\ClearedResponseCache;
use Spatie\ResponseCache\Facades\ResponseCache;

class EventServiceProvider extends ServiceProvider
{
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