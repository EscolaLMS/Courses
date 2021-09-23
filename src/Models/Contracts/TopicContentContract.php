<?php

namespace EscolaLms\Courses\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface TopicContentContract
{
    public static function rules(): array;

    public function topic(): MorphOne;
}
