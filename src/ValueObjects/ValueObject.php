<?php


namespace EscolaLms\Courses\ValueObjects;


abstract class ValueObject
{
    public static function make(...$args): ValueObject
    {
        $app = app(static::class);
        $app->build(...$args);
        return $app;
    }

    abstract public function toArray(): array;

}