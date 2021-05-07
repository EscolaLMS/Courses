<?php


namespace EscolaLms\Courses\ValueObjects\Contracts;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Courses\ValueObjects\ValueObject;

interface ValueObjectContract extends DtoContract
{
    public static function make(): ValueObject;
}