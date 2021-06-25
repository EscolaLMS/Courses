<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;
use Illuminate\Foundation\Http\FormRequest;

class SortAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $course = Course::find($this->input('course_id'));

        if (isset($user) && $user->can('update', $course)) {
            $class = $this->input('class');
            $ids = array_map(function ($order) {
                return $order[0];
            }, $this->input('orders'));

            switch ($class) {
                case "Lesson":
                    $lessons = Lesson::whereIn('id', $ids)->get();
                    foreach ($lessons as $lesson) {
                        if ($lesson->course_id !== $course->id) {
                            return false; // id from array is not matching course
                        }
                    }
                    break;

                case "Topic":
                    $topics = Topic::with('lesson')->whereIn('id', $ids)->get();
                    foreach ($topics as $topic) {
                        if ($topic->lesson->course_id !== $course->id) {
                            return false; // id from array is not matching course
                        }
                    }
                    break;
                }

            return true;
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'course_id' => ['required', 'numeric', 'exists:courses,id'],
            'class' => ['required','in:Lesson,Topic'],
            'orders' => ['required', 'array'],
            'orders.*' => ['required', 'array'],
        ];
    }
}
