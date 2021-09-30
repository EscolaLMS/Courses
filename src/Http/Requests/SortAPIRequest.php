<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Eloquent\Builder;
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
                    return Lesson::whereIn('id', $ids)->where('course_id', '<>', $course->getKey())->doesntExist();
                case "Topic":
                    return Topic::whereIn('id', $ids)->whereHas('lesson', function (Builder $query) use ($course) {
                        $query->where('course_id', '<>', $course->getKey());
                    })->doesntExist();
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
            'class' => ['required', 'in:Lesson,Topic'],
            'orders' => ['required', 'array'],
            'orders.*' => ['required', 'array'],
            'orders.*.0' => ['integer'],
            'orders.*.1' => ['integer'],
        ];
    }
}
