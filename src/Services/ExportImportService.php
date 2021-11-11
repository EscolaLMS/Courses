<?php

namespace EscolaLms\Courses\Services;

use EscolaLms\Courses\Http\Resources\CourseExportResource;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Repositories\Contracts\CourseRepositoryContract;
use EscolaLms\Courses\Services\Contracts\CourseServiceContract;
use EscolaLms\Courses\Services\Contracts\ExportImportServiceContract;
use Illuminate\Support\Facades\Storage;
use ZanySoft\Zip\Zip;

class ExportImportService implements ExportImportServiceContract
{
    private CourseRepositoryContract $courseRepository;
    private CourseServiceContract $courseService;

    public function __construct(
        CourseRepositoryContract $courseRepository,
        CourseServiceContract $courseService
    ) {
        $this->courseRepository = $courseRepository;
        $this->courseService = $courseService;
    }

    private function fixAllPathsBeforeZipping(int $courseId): void
    {
        $this->courseService->fixAssetPaths($courseId);
    }

    private function createExportJson(Course $course, $dirName): void
    {
        $program = CourseExportResource::make($course);

        $json = json_encode($program);

        Storage::put($dirName.'/content/content.json', $json);
    }

    private function copyCourseFilesToExportFolder(int $courseId): string
    {
        $dirName = 'exports/courses/'.$courseId;

        if (Storage::exists($dirName)) {
            Storage::deleteDirectory($dirName);
        }

        Storage::makeDirectory($dirName);

        $dirFrom = 'courses/'.$courseId;
        $dirTo = 'exports/courses/'.$courseId.'/content';
        $fromFiles = Storage::allFiles($dirFrom);

        foreach ($fromFiles as $fromFile) {
            $toFile = str_replace($dirFrom, $dirTo, $fromFile);
            Storage::copy($fromFile, $toFile);
        }

        return $dirName;
    }

    private function createZipFromFolder($dirName): string
    {
        $filename = uniqid(rand(), true).'.zip';

        $dirPath = Storage::path($dirName);
        $zip = Zip::create($dirPath.'/'.$filename);
        $zip->add($dirPath.'/content', true);
        $zip->close();

        Storage::deleteDirectory($dirName.'/content');

        return Storage::url($dirName.'/'.$filename);
    }

    public function export($courseId): string
    {
        $course = $this->courseRepository->findWith($courseId, ['*'], ['lessons.topics.topicable', 'scorm.scos']);
        $this->fixAllPathsBeforeZipping($courseId);
        $dirName = $this->copyCourseFilesToExportFolder($courseId);
        $this->createExportJson($course, $dirName);

        $zipUrl = $this->createZipFromFolder($dirName);

        return $zipUrl;
    }

    public function import($courseId): Course
    {
        return new Course();
    }
}
