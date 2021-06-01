<?php


namespace EscolaLms\Courses\Repositories\Criteria;

use EscolaLms\Core\Repositories\Criteria\Criterion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CourseSearch extends Criterion
{
    public function __construct($value = null)
    {
        parent::__construct(null, $value);
    }

    public function apply(Builder $query): Builder
    {
        return $query->where(function (Builder $query): Builder {
            $like = DB::connection()->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'pgsql' ? 'ILIKE' : 'LIKE';


            return $query
                ->where('courses.title', $like, '%' . $this->value . '%');
            // TODO: add `slug`
                //->orWhere('courses.slug', $like, '%' . $this->value . '%');
        });
    }
}
