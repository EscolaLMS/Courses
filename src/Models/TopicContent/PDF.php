<?php

namespace EscolaLms\Courses\Models\TopicContent;

use EscolaLms\Courses\Models\AbstractContent;
use EscolaLms\Courses\Models\Topic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *      schema="TopicPDF",
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

class PDF extends AbstractContent
{
    use HasFactory;

    public $table = 'topic_pdfs';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'value'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'value' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'value' => 'required|file|mimes:pdf'
    ];

    protected $appends = ['url'];

    public function topic()
    {
        return $this->morphOne(Topic::class, 'topicable');
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\PdfFactory::new();
    }

    public static function createResourceFromRequest($input, $topicId): array
    {
        $tmpFile = $input['value']->getPathName();
        $path = $input['value']->store("public/topic/$topicId/pdfs");

        return [
            'value' => $path,
        ];
    }

    public function getUrlAttribute()
    {
        return  url(Storage::url($this->attributes['value']));
    }
}
