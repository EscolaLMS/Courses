<?php


namespace EscolaLms\Courses\Http\Requests;


use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Rules\ValidEnum;
use Illuminate\Foundation\Http\FormRequest;

class CourseProgressAPIRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'progress' => 'array',
            'progress.*.lecture_id' => ['numeric', 'exists:curriculum_lectures_quiz,lecture_quiz_id'],
            'progress.*.status' => ['numeric', new ValidEnum(ProgressStatus::class)]
        ];
    }
}
