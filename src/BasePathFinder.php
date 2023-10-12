<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner;

use RuntimeException;

use function getcwd;

class BasePathFinder
{
    public function findBasePath(): string
    {
        $basePath = getcwd();
        if ($basePath === false) {
            throw new RuntimeException('getcwd() returned false.');
        }
        return $basePath;
    }
}
