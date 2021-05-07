<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
            $flat_ids = array_map(fn ($cat) => $cat->id, $flat);
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

        /** search by TAG */

        if (isset($search['tag']) && $search['tag']) {
            $query->whereHas('tags', function (Builder $query) use ($search) {
                $query->where('title', '=', $search['tag']);
            });
            unset($search['tag']);
        }


        return isset($search['tag']) ? $query->with('tags') : $query;
    }

    public function attachCategory(Course $course, Category $category) : bool
    {
        return $course->categories()->save($category)->getKey();
    }

    public function attachTag(Course $course, Tag $tag) : bool
    {
        return $course->tags()->save($tag)->getKey();
    }

    /**
     * Find model record for given id with relations
     *
     * @param int $id
     * @param array $columns
     * @param array $with relations
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function findWith(int $id, array $columns = ['*'], array $with = []): ?Course
    {
        $query = $this->model->newQuery()->with($with);

        return $query->find($id, $columns);
    }

    /**
     * Create model record
     *
     * @param array $input
     *
     * @return Model
     */
    public function create(array $input): Course
    {
        $input['author_id'] = Auth::id();
        $model = $this->model->newInstance($input);

        $model->save();

        $update = [];
        $courseId = $model->id;

        if (isset($input['video'])) {
            $update['video_path'] = $input['video']->store("public/course/$courseId/videos");
        }

        if (isset($input['image'])) {
            $update['image_path'] = $input['image']->store("public/course/$courseId/images");
        }

        if (count($update)) {
            $model->update($update);
        }

        return $model;
    }

    /**
     * Update model record for given id
     *
     * @param array $input
     * @param int $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model
     */
    public function update(array $input, int $id): Course
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        if (isset($input['video'])) {
            $input['video_path'] = $input['video']->store("public/course/$id/videos");
        }

        if (isset($input['image'])) {
            $input['image_path'] = $input['image']->store("public/course/$id/images");
        }

        $model->fill($input);

        $model->save();

        return $model;
    }
}
