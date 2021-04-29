<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\BaseRepository;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class CourseRepository
 * @package EscolaLms\Courses\Repositories
 * @version April 27, 2021, 11:19 am UTC
*/

class CourseRepository extends BaseRepository implements CourseRepositoryContract
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'title',
        'summary',
        'image_path',
        'video_path',
        'base_price',
        'duration',
        'author_id'
    ];

    /**
     * Recursive flatten object by given $key
     * @param object $input an object with children key
     * @param string $key children key
     */
    public static function flatten($input, $key)
    {
        $output = [];
        foreach ($input as $object) {
            $children = isset($object->$key) ? $object->$key : [];
            $object->$key = [];
            $output[] = $object;
            $children = self::flatten($children, $key);
            foreach ($children as $child) {
                $output[] = $child;
            }
        }
        return $output;
    }


    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Course::class;
    }

    /**
     * @return Builder
     */
    public function queryAll(): Builder
    {
        return $this->model->newQuery()
            ->select('courses.*', 'categories.name as category_name')
            ->leftJoin('category_course', 'category_course.course_id', '=', 'courses.id')
            ->leftJoin('categories', 'categories.id', '=', 'category_course.category_id');
    }

    public function allQueryBuilder(array $search = [], ?int $skip = null, ?int $limit = null, array $criteria = []): Builder
    {
        if (isset($search) && isset($search['title'])) {
            $search['title'] = ['ILIKE', "%" . $search['title'] . "%"];
        }

        /** search main category and all subcategories */
        if (isset($search) && isset($search['category_id'])) {
            $collection = Category::where('id', $search['category_id'])->with('children')->get();
            $flat = self::flatten($collection, 'children');
            $flat_ids = array_map(fn($cat) => $cat->id, $flat);
            $flat_ids[] = $search['category_id'];
            unset($search['category_id']);
        }

        $query = $this->allQuery($search, $skip, $limit);

        if (isset($flat_ids)) {
            $query->leftJoin('category_course', 'category_course.course_id', '=', 'courses.id')
                    ->leftJoin('categories', 'categories.id', '=', 'category_course.category_id')
                    ->whereIn('categories.id', $flat_ids);
        }

        if (!empty($criteria)) {
            $query = $this->applyCriteria($query, $criteria);
        }

        return $query;
    }

}
