<?php

namespace EscolaLms\Courses\Events\Contracts;

use EscolaLms\Core\Models\User;

interface BadgeEventContract
{
    public function getUser(): User;
}