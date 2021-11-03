<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
        'author_id',
        'active',
        'scorm_id',
        'poster_path',
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

    public function allQueryBuilder(array $search = [], array $criteria = []): Builder
    {
        /** search main category and all subcategories */
        if (isset($search) && isset($search['category_id'])) {
            $collection = Category::where('id', $search['category_id'])->with('children')->get();
            $flat = self::flatten($collection, 'children');
            $flat_ids = array_map(fn ($cat) => $cat->id, $flat);
            $flat_ids[] = $search['category_id'];
            unset($search['category_id']);
        }

        $query = $this->allQuery($search);

        if (isset($flat_ids)) {
            $query = $query->whereHas('categories', function (Builder $query) use ($flat_ids) {
                $query->whereIn('categories.id', $flat_ids);
            });
        }

        if (!empty($criteria)) {
            $query = $this->applyCriteria($query, $criteria);
        }

        /** search by id in array */
        if (isset($search['ids']) && !empty($search['ids'])) {
            $query->whereIn('id', array_filter($search['ids'], 'is_numeric'));
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

    /**
     * Find model record for given id with relations
     *
     * @param int $id
     * @param array $columns
     * @param array $with relations
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    public function findWith(int $id, array $columns = ['*'], array $with = [], array $withCount = []): ?Course
    {
        $query = $this->model->newQuery()->with($with)->withCount($withCount);

        return $query->find($id, $columns);
    }

    /**
     * Create model record
     * 
     * @return Course
     */
    public function create(array $input): Model
    {
        if (!isset($input['author_id']) || !(Auth::user() && Auth::user()->hasRole(UserRole::ADMIN))) {
            $input['author_id'] = Auth::id();
        }

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

        if (isset($input['poster'])) {
            $update['poster_path'] = $input['poster']->store("public/course/$courseId/posters");
        }

        if (count($update)) {
            $model->update($update);
        }

        return $model;
    }

    /**
     * Update model record for given id
     *
     * @return Course
     */
    public function update(array $input, int $id): Model
    {
        $query = $this->model->newQuery();

        $model = $query->findOrFail($id);

        if (isset($input['author_id']) && Auth::user() && !Auth::user()->hasRole(UserRole::ADMIN)) {
            $input['author_id'] = Auth::id();
        }

        if (isset($input['video'])) {
            $input['video_path'] = $input['video']->store("public/course/$id/videos");
        }

        if (isset($input['image'])) {
            $input['image_path'] = $input['image']->store("public/course/$id/images");
        }

        if (isset($input['poster'])) {
            $input['poster_path'] = $input['poster']->store("public/course/$id/posters");
        }

        if (isset($input['categories']) && is_array($input['categories'])) {
            $model->categories()->sync($input['categories']);
        }

        if (isset($input['tags']) && is_array($input['tags'])) {

            /** this is actually replacing the tags, even when you do send exactly the same  */
            $model->tags()->delete();

            $tags = array_map(function ($tag) {
                return ['title' => $tag];
            }, $input['tags']);

            $model->tags()->createMany($tags);
        }

        $model->fill($input);

        $model->save();

        return $model;
    }

    public function getById(int $id): Course
    {
        return $this->model->newQuery()->find($id);
    }

    public function delete(int $id): ?bool
    {
        $course = $this->findWith($id, ['*'], ['lessons.topics']);
        foreach ($course->lessons as $lesson) {
            foreach ($lesson->topics as $topic) {
                $topic->topicable()->delete();
                $topic->delete();
            }
        }
        $course->lessons()->delete();
        $course->delete();
        return true;
    }

    private function tutors()
    {
        return User::whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('courses')
                ->where('active', 1)
                ->whereColumn('courses.author_id', 'users.id');
        })->select(['id', 'first_name', 'last_name', 'email', 'path_avatar', 'bio']);
    }

    public function findTutors(): Collection
    {
        return $this->tutors()->get();
    }

    public function findTutor($id): ?User
    {
        return $this->tutors()->where('id', $id)->first();
    }
}
