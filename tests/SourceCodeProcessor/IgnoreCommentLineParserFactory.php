<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\SourceCodeProcessor;

use IODigital\CodeSnifferBaseliner\SourceCodeProcessor\InstructionCommentLineParser;

class IgnoreCommentLineParserFactory
{
    public static function create(): InstructionCommentLineParser
    {
        return new InstructionCommentLineParser();
    }
}
