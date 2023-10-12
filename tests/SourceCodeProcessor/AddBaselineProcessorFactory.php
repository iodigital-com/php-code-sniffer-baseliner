<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\AddBaselineProcessor;
use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;

class AddBaselineProcessorFactory
{
    public static function create(
        ?InstructionCommentLineParser $ignoreCommentLineParser = null
    ): AddBaselineProcessor {
        return new AddBaselineProcessor(
            $ignoreCommentLineParser !== null ? $ignoreCommentLineParser : IgnoreCommentLineParserFactory::create()
        );
    }
}
