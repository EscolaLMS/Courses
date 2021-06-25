<?php

namespace EscolaLms\Courses\Repositories\Criteria\Primitives;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;

class LikeCriterion extends Criterion
{

    public function apply(Builder $query): Builder
    {
        if (!$this->value) {
            return $query;
        }

        return $query->where($this->key, 'ILIKE', '%' . $this->value . '%');
    }
}