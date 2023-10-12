<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\Tests\Filesystem;

use IODigital\CodeSnifferBaseliner\Filesystem\Filesystem;

class MemoryFilesystem implements Filesystem
{
    /**
     * @var array<string>
     */
    private $fileContentsByFilename = [];

    public function readContents(string $filename): string
    {
        return $this->fileContentsByFilename[$filename];
    }

    public function replaceContents(string $filename, string $contents): void
    {
        $this->fileContentsByFilename[$filename] = $contents;
    }
}
