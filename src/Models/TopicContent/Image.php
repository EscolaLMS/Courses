<?php

namespace EscolaLms\Courses\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *      schema="TopicImage",
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

class Image extends AbstractTopicFileContent
{
    use HasFactory;

    public $table = 'topic_images';

    protected $fillable = [
        'value',
        'width',
        'height',
    ];

    protected $casts = [
        'id' => 'integer',
        'value' => 'string',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public static function rules(): array
    {
        return  [
            'value' => ['required', 'image'],
            'width' => ['sometimes', 'integer'],
            'height' => ['sometimes', 'integer'],
        ];
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\ImageFactory::new();
    }

    public function getStoragePathFinalSegment(): string
    {
        return 'image';
    }

    protected function processUploadedFiles(): void
    {
        $sizes = getimagesize(Storage::path($this->value));
        if ($sizes) {
            $this->width = $sizes[0];
            $this->height = $sizes[1];
        }
    }
}
