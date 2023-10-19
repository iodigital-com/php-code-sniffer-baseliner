<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\Filesystem;

use IODigital\CodeSnifferBaseliner\Filesystem\Filesystem;

class FilesystemFactory
{
    public static function create(): Filesystem
    {
        return new MemoryFilesystem();
    }
}
