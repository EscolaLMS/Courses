<?php

namespace EscolaLms\Courses\Http\Requests;

use EscolaLms\Courses\Models\Lesson;
use Illuminate\Foundation\Http\FormRequest;

class CloneLessonAPIRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        $lesson = $this->getLesson();

        if (is_null($lesson)) {
            return true; // controller will fire 404 error
        }

        return isset($user) && $user->can('clone', $lesson);
    }

    public function rules(): array
    {
        return [];
    }

    public function getLesson(): ?Lesson
    {
        return Lesson::find($this->route('id'));
    }
}
