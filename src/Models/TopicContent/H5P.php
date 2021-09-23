<?php

namespace EscolaLms\Courses\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicH5P",
 *      required={"value"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          @OA\Schema(
 *             type="integer",
 *         )
 *      ),
 *      @OA\Property(
 *          property="value",
 *          description="value",
 *          type="string"
 *      )
 * )
 */

class H5P extends AbstractTopicContent
{
    use HasFactory;

    public $table = 'topic_h5ps';

    public static function rules(): array
    {
        return [
            'value' => ['required', 'integer', 'exists:hh5p_contents,id']
        ];
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\H5PFactory::new();
    }
}
