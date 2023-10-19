<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\Util;

use IODigital\CodeSnifferBaseliner\Util\OutputWriter;

class OutputWriterFactory
{
    public static function create(): OutputWriter
    {
        return new OutputWriter();
    }
}
