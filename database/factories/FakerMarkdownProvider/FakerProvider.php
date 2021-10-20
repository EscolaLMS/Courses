<?php

namespace EscolaLms\Courses\Database\Factories\FakerMarkdownProvider;

use DavidBadura\FakerMarkdownGenerator\FakerProvider as FakerMarkdownGeneratorFakerProvider;
use DavidBadura\MarkdownBuilder\MarkdownBuilder;

class FakerProvider extends FakerMarkdownGeneratorFakerProvider
{
    public static function markdown()
    {
        $parts = [];

        do {
            $parts[] = self::markdownH1();

            if (self::randomDigit() > 3) {
                $parts[] = self::markdownP();
            }

            if (self::randomDigit() > 3) {
                $parts[] = self::markdownBlockquote();
            }

            if (self::randomDigit() > 3) {
                $parts[] = self::markdownBulletedList();
            }

            if (self::randomDigit() > 3) {
                $parts[] = self::markdownNumberedList();
            }
        } while (self::randomDigit() > 5 || count($parts) < 3);

        return implode("\n\n", $parts);
    }

    public static function markdownBlockqoute($maxNbChars = 200)
    {
        return self::markdownBlockquote($maxNbChars);
    }

    public static function markdownBlockquote($maxNbChars = 200)
    {
        return (new MarkdownBuilder())->blockquote(self::text($maxNbChars))->getMarkdown();
    }
}
