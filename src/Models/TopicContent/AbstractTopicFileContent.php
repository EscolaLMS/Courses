<?php

namespace EscolaLms\Courses\Models\TopicContent;

use EscolaLms\Courses\Exceptions\TopicException;
use EscolaLms\Courses\Models\Contracts\TopicFileContentContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

abstract class AbstractTopicFileContent extends AbstractTopicContent implements TopicFileContentContract
{
    protected $appends = ['url'];

    public function getFileKeys(): array
    {
        $rules = $this->rules();
        return array_keys(array_filter($rules, function ($key_rules) {
            if (is_array($key_rules)) {
                return in_array('file', $key_rules) || in_array('image', $key_rules);
            }
            return (strpos('file', $key_rules) !== false) || (strpos('image', $key_rules));
        }));
    }

    public function generateStoragePath(?string $base_path = null): string
    {
        if (empty($base_path)) {
            if ($this->topic) {
                $base_path = $this->topic->getStorageDirectoryAttribute();
            } else {
                $base_path = "topic-content/" . $this->getKey() . "/";
            }
        }
        return $base_path . $this->getStoragePathFinalSegment();
    }

    public function getUrlAttribute(): string
    {
        return url(Storage::url($this->value));
    }

    public function storeUploadsFromRequest(FormRequest $request, ?string $path = null): self
    {
        foreach ($this->getFileKeys() as $file_key) {
            $file = $request->file($file_key);
            if ($file) {
                $this->storeUpload($file, $file_key, $path);
            }
        }
        $this->processUploadedFiles();
        return $this;
    }

    protected function processUploadedFiles(): void
    {
    }

    protected function storeUpload(UploadedFile $file, string $key = 'value', ?string $path = null): string
    {
        $this->{$key} = $file->storePublicly($this->generateStoragePath($path));
        return $this->{$key};
    }
}
