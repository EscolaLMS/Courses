<?php

namespace EscolaLms\Courses\Models\TopicContent;

use EscolaLms\Courses\Models\Contracts\TopicFileContentContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

abstract class AbstractTopicFileContent extends AbstractTopicContent implements TopicFileContentContract
{
    protected $appends = ['url'];

    public function getFileKeyNames(): array
    {
        return Collection::make($this->rules())
            ->filter(function ($field_rules) {
                if (is_array($field_rules)) {
                    return in_array('file', $field_rules) || in_array('image', $field_rules);
                }

                return strpos('file', $field_rules) !== false || strpos('image', $field_rules) !== false;
            })
            ->keys()
            ->toArray();
    }

    public function generateStoragePath(?string $base_path = null): string
    {
        if (empty($base_path)) {
            if ($this->topic) {
                $base_path = $this->topic->storage_directory;
            } else {
                $base_path = 'topic-content/'.$this->getKey().'/';
            }
        }

        return $base_path.$this->getStoragePathFinalSegment();
    }

    public function getUrlAttribute(): string
    {
        if ($this->value ?? null) {
            $value = Storage::url(trim($this->value, '/'));
            return preg_match('/^(http|https):.*$/', $value, $oa) ?
                $value :
                url($value);
        }
        return '';
    }

    public function storeUploadsFromRequest(FormRequest $request, ?string $path = null): self
    {
        foreach ($this->getFileKeyNames() as $file_key) {
            if ($request->hasFile($file_key)) {
                $this->storeUpload($request->file($file_key), $file_key, $path);
            }
        }
        $this->processUploadedFiles();

        return $this;
    }

    protected function processUploadedFiles(): void
    {
        // do something in child classes
    }

    protected function storeUpload(UploadedFile $file, string $key = 'value', ?string $path = null): string
    {
        $this->{$key} = $file->storePublicly($this->generateStoragePath($path));

        return $this->{$key};
    }
}
