<?php

declare(strict_types=1);

namespace IODigital\CodeSnifferBaseliner\PhpCodeSnifferRunner;

use function getenv;

class PhpCodeSnifferBinaryLocatorFactory
{
    public function createFromEnvironment(): PhpCodeSnifferBinaryLocator
    {
        return new PhpCodeSnifferBinaryLocator(getenv());
    }
}
