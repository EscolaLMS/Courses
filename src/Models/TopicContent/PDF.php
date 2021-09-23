<?php

namespace EscolaLms\Courses\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

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
class PDF extends AbstractTopicFileContent
{
    use HasFactory;

    public $table = 'topic_pdfs';

    public static function rules(): array
    {
        return [
            'value' => ['required', 'file', 'mimes:pdf']
        ];
    }

    protected static function newFactory()
    {
        return \EscolaLms\Courses\Database\Factories\TopicContent\PdfFactory::new();
    }

    public function getStoragePathFinalSegment(): string
    {
        return 'pdf';
    }
}
