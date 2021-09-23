<?php

namespace EscolaLms\Courses\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicOEmbed",
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

class OEmbed extends AbstractTopicContent
{
    use HasFactory;

    public $table = 'topic_oembeds';

    /**
     * Validation rules
     *
     * @var array
     */
    public static function rules(): array
    {
        return [
            'value' => ['required', 'string']
        ];
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\OEmbedFactory::new();
    }
}
