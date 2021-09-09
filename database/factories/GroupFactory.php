<?php


namespace EscolaLms\Courses\Database\Factories;

use Database\Factories\EscolaLms\Auth\Models\GroupFactory as AuthGroupFactory;
use EscolaLms\Courses\Models\Group;

class GroupFactory extends AuthGroupFactory
{
    protected $model = Group::class;
}
