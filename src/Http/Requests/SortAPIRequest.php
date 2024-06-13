<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Eloquent\Collection;
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

                    return $lessons->count() === count($ids)
                        && $lessons->pluck('course_id')->unique()->count() === 1
                        && $lessons->pluck('parent_lesson_id')->unique()->count() === 1;
                case 'Topic':
                    /** @var Collection<int, Topic> $topics */
                    $topics = Topic::whereIn('id', $ids)->get();

                    if ($topics->count() !== count($ids) || $topics->pluck('lesson_id')->unique()->count() != 1) {
                        return false;
                    }
                    $lesson = Lesson::find($topics->first()->lesson_id);

                    return $lesson && $lesson->course_id === $course->getKey();
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
}
