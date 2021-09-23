<?php

namespace EscolaLms\Courses\Models\Contracts;

use Illuminate\Foundation\Http\FormRequest;

interface TopicFileContentContract
{
    public function getFileKeys(): array;
    public function getUrlAttribute(): string;
    public function generateStoragePath(?string $base_path = null): string;
    public function getStoragePathFinalSegment(): string;
    public function storeUploadsFromRequest(FormRequest $request, ?string $path = null): self;
}
