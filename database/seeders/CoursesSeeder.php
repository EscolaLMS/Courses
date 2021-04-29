<?php

namespace EscolaLms\Courses\Database\Seeders;

use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicContent\RichText;
use EscolaLms\Courses\Models\TopicContent\Audio;
use EscolaLms\Courses\Models\TopicContent\Video;
use EscolaLms\Courses\Models\TopicContent\Image;
use EscolaLms\Courses\Models\TopicContent\H5P;


use Illuminate\Database\Seeder;

class CoursesSeeder extends Seeder
{
    private function getRandomRichContent()
    {
        // TODO: instruduce a abstract TopicContent class
        // all below will extends this calls
        // and the list is from that abstraction
        switch (rand(1, 5)) {
            case 1:
                return RichText::factory()->create();
            case 2:
                return Audio::factory()->create();
            case 3:
                return Video::factory()->create();
            case 4:
                return Image::factory()->create();
            case 5:
                return H5P::factory()->create();
        }
    }

    public function run()
    {
        Course::factory()
        ->count(1)
        ->has(Lesson::factory()
            ->has(
                Topic::factory()->afterCreating(function ($topic) {
                    $content = $this->getRandomRichContent();
                    $topic->topicable()->associate($content)->save();
                })
                /*
                ->each(function ($topic) {
                    $topic->for(TopicRichText::factory()->create(), 'topicable');
                })
                */
            )
            ->count(rand(1, 10)))

        ->create();
    }
}
