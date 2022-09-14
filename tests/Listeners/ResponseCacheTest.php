<?php

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Spatie\ResponseCache\Events\ClearedResponseCache;

class ResponseCacheTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    public function testClearResponseCacheAfterCreated(): void
    {
        Event::fake([ClearedResponseCache::class]);
        Course::factory()->create();
        Event::assertDispatched(ClearedResponseCache::class);
    }

    public function testClearResponseCacheAfterUpdated(): void
    {
        $course = Course::factory()->create();
        Event::fake([ClearedResponseCache::class]);

        $course->update([
            'title' => $this->faker->title,
        ]);

        Event::assertDispatched(ClearedResponseCache::class);
    }

    public function testClearResponseCacheAfterDeleted(): void
    {
        $course = Course::factory()->create();
        Event::fake([ClearedResponseCache::class]);
        $course->delete();
        Event::assertDispatched(ClearedResponseCache::class);
    }
}