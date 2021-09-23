<?php

namespace EscolaLms\Courses\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicAudio",
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

class Audio extends AbstractTopicFileContent
{
    use HasFactory;

    public $table = 'topic_audios';

    protected $fillable = [
        'value',
        'length'
    ];

    protected $casts = [
        'id' => 'integer',
        'value' => 'string',
        'length' => 'integer',
    ];

    public static function rules(): array
    {
        return [
            'value' => ['required', 'file', 'mimes:mp3,ogg'],
            'length' => ['sometimes', 'integer'],
        ];
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\AudioFactory::new();
    }

    protected function processUploadedFiles(): void
    {
        $this->length = 0;
    }

    public function getStoragePathFinalSegment(): string
    {
        return 'audio';
    }
}
