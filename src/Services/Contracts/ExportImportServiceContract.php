<?php

namespace EscolaLms\Courses\Services\Contracts;

use EscolaLms\Courses\Models\Course;

interface ExportImportServiceContract
{
    public function export(int $courseId): string;

    public function import(string $path): Course;
}
