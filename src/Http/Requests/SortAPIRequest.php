<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class SortAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $course = Course::find($this->input('course_id'));
        if (isset($user) && $user->can('update', $course)) {
            $class = $this->input('class');
            $ids = array_map(function ($order) {
                return $order[0];
            }, $this->input('orders'));
            switch ($class) {
                case 'Lesson':
                    $lessons = Lesson::whereIn('id', $ids)->get();
                    if ($lessons->count() != count($ids)) {
                        return false;
                    }
                    if ($lessons->pluck('order_id')->unique()->count() > 1 || $lessons->pluck('parent_lesson_id')->unique()->count() > 1) {
                        return false;
                    }

                    return $this->isLessonInCourse($lessons->first(), $course);
                case 'Topic':
                    $topics = Topic::whereIn('id', $ids)->get();

                    if ($topics->count() !== count($ids) || $topics->pluck('lesson_id')->unique()->count() > 1) {
                        return false;
                    }

                    return $this->isLessonInCourse($topics->first()->lesson, $course);
            }

            return true;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'numeric', 'exists:courses,id'],
            'class' => ['required', 'in:Lesson,Topic'],
            'orders' => ['required', 'array'],
            'orders.*' => ['required', 'array'],
            'orders.*.0' => ['integer'],
            'orders.*.1' => ['integer'],
        ];
    }

    private function isLessonInCourse(Lesson $lesson, Course $course): bool
    {
        if ($lesson->parentLesson) {
            return $this->isLessonInCourse($lesson->parentLesson, $course);
        }

        return $lesson->course_id === $course->getKey();
    }
}
