<?php

namespace EscolaLms\Courses\Services;

use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;

class CacheGetRequestService extends CacheAllSuccessfulGetRequests
{
    public function useCacheNameSuffix(Request $request): string
    {
        return isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] . '_' . parent::useCacheNameSuffix($request) : parent::useCacheNameSuffix($request);
    }
}
