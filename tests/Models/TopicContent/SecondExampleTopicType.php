<?php

namespace EscolaLms\Courses\Tests\Models\TopicContent;

use EscolaLms\Courses\Models\TopicContent\AbstractTopicContent;
use EscolaLms\Courses\Tests\Database\Factories\ExampleTopicTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecondExampleTopicType extends AbstractTopicContent
{
    use HasFactory;

    public $table = 'topic_example';

    protected static function newFactory()
    {
        return ExampleTopicTypeFactory::new();
    }

    public function getMorphClass()
    {
        return self::class;
    }
}
