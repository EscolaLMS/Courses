<?php

namespace EscolaLms\Courses\Models\Traits;

use Spatie\ResponseCache\Events\ClearedResponseCache;
use Spatie\ResponseCache\Events\ClearingResponseCache;
use Spatie\ResponseCache\Facades\ResponseCache;

trait ClearsResponseCache
{
    public static function bootClearsResponseCache()
    {
        self::created(function () {
            self::clearsResponseCache();
        });

        self::updated(function () {
            self::clearsResponseCache();
        });

        self::deleted(function () {
            self::clearsResponseCache();
        });
    }

    private static function clearsResponseCache(): void
    {
        event(ClearingResponseCache::class);
        ResponseCache::clear();
        event(ClearedResponseCache::class);
    }
}
