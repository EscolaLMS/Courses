<?php

namespace EscolaLms\Courses\Repositories;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicResource;
use EscolaLms\Courses\Repositories\BaseRepository;
use EscolaLms\Courses\Repositories\Contracts\TopicResourceRepositoryContract;
use Exception;
use Illuminate\Contracts\Filesystem\FileExistsException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TopicResourceRepository extends BaseRepository implements TopicResourceRepositoryContract
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

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
        return TopicResource::class;
    }

    public function storeUploadedResourceForTopic(Topic $topic, UploadedFile $file): TopicResource
    {
        $course = $topic->lesson->course;
        $name = $file->getClientOriginalName();
        $destination = sprintf('courses/%d/topic/%d/resources', $course->getKey(), $topic->getKey());
        $path = Storage::putFileAs($destination, $file, $name);

        return $this->create([
            'topic_id' => $topic->getKey(),
            'path' => $path,
            'name' => $name,
        ]);
    }

    public function delete(int $id): ?bool
    {
        $topicResource = $this->model->query()->findOrFail($id);
        $path = $topicResource->path;

        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        return $topicResource->delete();
    }

    public function rename(int $id, string $name): bool
    {
        $topicResource = $this->model->newQuery()->findOrFail($id);
        return $this->renameModel($topicResource, $name);
    }

    public function renameModel(TopicResource $model, string $name): bool
    {
        $newExtension = Str::afterLast($name, '.');
        if (empty($newExtension) || $newExtension === $name) {
            $oldExtension = Str::afterLast($model->name, '.');
            $name = $name . '.' . $oldExtension;
        }

        $oldPath = $model->path;
        $newPath = Str::replace($model->name, $name, $model->path);

        if (Storage::exists($oldPath) && !Storage::exists($newPath)) {
            Storage::move($oldPath, $newPath); // will throw FileExistsException if file at newPath exists
            $model->name = $name;
            $model->path = $newPath;
            $model->save();
            return true;
        }

        return false;
    }
}
